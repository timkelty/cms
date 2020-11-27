<?php
/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license https://craftcms.github.io/license/
 */

namespace craft\gql\base;


use craft\gql\ArgumentManager;

/**
 * Class ArgumentHandler
 *
 * @author Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @since 3.6.0
 */
abstract class ArgumentHandler implements ArgumentHandlerInterface
{
    /** @var ArgumentManager */
    protected $argumentManager;

    /** @var string */
    protected $argumentName;

    /**
     * Construct the argument handler and save a reference to the argument manager.
     *
     * @param ArgumentManager $argumentManager
     */
    public function __construct(ArgumentManager $argumentManager) {
        $this->argumentManager = $argumentManager;
    }

    /**
     * Handle a single argument value
     * @param $argumentValue
     * @return mixed
     */
    abstract protected function handleArgument($argumentValue);

    /**
     * @inheritdoc
     */
    public function handleArgumentCollection(array $argumentList = []): array
    {
        $argumentList[$this->argumentName] = $this->handleArgument($this->argumentName);

        return $argumentList;
    }
}
