<?php

namespace Zxin\DataStruct;

use ReflectionClass;
use RuntimeException;

use function fclose;
use function feof;
use function fgets;
use function fopen;
use function is_array;
use function strrpos;
use function substr;
use function token_get_all;

class ReflectionClassExpansion
{
    protected $refl;
    protected $useStatements;
    protected $fastUseMapping = [];

    public function __construct(ReflectionClass $reflectionClass)
    {
        $this->refl = $reflectionClass;
    }

    /**
     * @return string
     */
    public function readHeadSource()
    {
        $file = fopen($this->refl->getFileName(), 'r');
        $startLine = $this->refl->getStartLine();
        $line = 0;
        $source = '';
        while (!feof($file) && ++$line < $startLine) {
            $source .= fgets($file);
        }
        fclose($file);
        return $source;
    }

    /**
     * @param string $source
     * @return array
     */
    public function analysisSource(string $source)
    {
        $tokens = token_get_all($source);

        $useStatements = [];

        $startAnalysisUse = false;
        $blockPrefix = '';
        $existAlias = false;
        $currUseClass = [
            'class' => '',
            'alias' => null,
        ];

        foreach ($tokens as $token) {
            if (is_array($token)) {
                if ($token[0] === T_USE) {
                    $startAnalysisUse = true;
                    continue;
                }
                if ($startAnalysisUse) {
                    if ($existAlias && T_STRING === $token[0]) {
                        $currUseClass['alias'] = $token[1];
                        $existAlias = false;
                        continue;
                    }
                    switch ($token[0]) {
                        case T_FUNCTION:
                            $startAnalysisUse = false;
                            break;
                        case T_STRING:
                        case T_NS_SEPARATOR:
                            $currUseClass['class'] .= $token[1];
                            break;
                        case T_AS:
                            $existAlias = true;
                            break;
                        default:
                            if (PHP_VERSION_ID >= 80000) {
                                if (T_NAME_FULLY_QUALIFIED === $token[0]
                                    || T_NAME_RELATIVE === $token[0]
                                    || T_NAME_QUALIFIED === $token[0]
                                ) {
                                    $currUseClass['class'] .= $token[1];
                                    break;
                                }
                            }
                    }
                }
                continue;
            }
            if ($startAnalysisUse) {
                if ($token === '{') {
                    // 遇到块开始则当前类为块前缀
                    $blockPrefix = $currUseClass['class'];
                    $currUseClass['class'] = '';
                    continue;
                }
                // 遇到断句则当前类结束
                if ($token === ',' || $token === ';') {
                    $currUseClass['class'] = $blockPrefix . $currUseClass['class'];
                    $useStatements[] = $currUseClass;
                    $currUseClass['class'] = '';
                    $currUseClass['alias'] = null;
                }
                // 遇到分号代表行或块结束
                if ($token === ';') {
                    $blockPrefix = '';
                    $startAnalysisUse = false;
                    continue;
                }
            }
        }

        return $useStatements;
    }

    /**
     * Parse class file and get use statements from current namespace.
     * @return array
     */
    protected function parseUseStatements()
    {
        if ($this->useStatements) {
            return $this->useStatements;
        }

        if (!$this->refl->isUserDefined()) {
            throw new RuntimeException('Must parse use statements from user defined classes.');
        }

        $source = $this->readHeadSource();
        $this->useStatements = $this->analysisSource($source);

        return $this->useStatements;
    }

    /**
     * Return array of use statements from class.
     * @return array
     */
    public function getUseStatements()
    {
        return $this->parseUseStatements();
    }

    /**
     * @return array
     */
    public function getFastUseMapping()
    {
        $this->parseUseStatements();

        if (empty($this->fastUseMapping)) {
            foreach ($this->useStatements as $use) {
                if (null === $use['alias']) {
                    $alias = substr($use['class'], strrpos($use['class'], '\\') + 1);
                    $this->fastUseMapping[$alias] = $use['class'];
                } else {
                    $this->fastUseMapping[$use['alias']] = $use['class'];
                }
            }
        }
        return $this->fastUseMapping;
    }

    /**
     * Check if class is using a class or an alias of a class.
     * @param string $class
     * @return boolean
     */
    public function hasUseStatement($class)
    {
        $this->getFastUseMapping();

        return isset($this->fastUseMapping[$class]);
    }
}
