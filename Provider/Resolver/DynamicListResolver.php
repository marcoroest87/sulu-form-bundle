<?php

namespace L91\Sulu\Bundle\FormBundle\Provider\Resolver;

/**
 * Implementation for dynamic list resolver by separate arrays by specific delimiter.
 */
class DynamicListResolver implements DynamicListResolverInterface
{
    /**
     * @var string
     */
    protected $delimiter;

    /**
     * DynamicListResolver constructor.
     *
     * @param string $delimiter
     */
    protected function __construct(
        $delimiter = PHP_EOL
    ) {
        $this->delimiter = $delimiter;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(array $entry)
    {
        $singleEntry = [];

        foreach ($entry as $key => $value) {
            $singleEntry[$key] = $this->toString($value);
        }

        return [ $singleEntry ];
    }

    /**
     * Convert value to string.
     *
     * @param string|array $value
     *
     * @return string
     *
     * @throws \Exception
     */
    protected function toString($value)
    {
        if (is_string($value)) {
            return $value;
        }

        if (!is_array($value)) {
            throw new \Exception('Invalid value for list resolver.');
        }

        return implode($this->delimiter, $value);
    }
}
