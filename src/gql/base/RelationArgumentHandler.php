<?php
/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license https://craftcms.github.io/license/
 */

namespace craft\gql\base;


use Craft;
use craft\elements\db\ElementQuery;
use craft\elements\db\ElementQueryInterface;
use craft\helpers\ArrayHelper;
use craft\helpers\StringHelper;

/**
 * Class RelationArgumentHandler
 *
 * @author Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @since 3.6.0
 */
abstract class RelationArgumentHandler extends ArgumentHandler
{
    /** @var array  */
    private $_memoizedValues = [];

    /**
     * @inheritdoc
     */
    public function getCanMemoizeResult(): bool
    {
        return true;
    }

    /**
     * Get the IDs of elements returned by configuring the provided element query with given criteria.
     *
     * @param ElementQueryInterface $elementQuery
     * @param array $criteria
     * @return int[]
     */
    protected function getIds(ElementQueryInterface $elementQuery, array $criteria = []): array
    {
        /** @var ElementQuery $elementQuery */
        $elementQuery = Craft::configure($elementQuery, $criteria);

        return $elementQuery->ids();
    }

    /**
     * @inheritdoc
     */
    public function handleArgumentCollection(array $argumentList = []): array
    {
        if (!array_key_exists($this->argumentName, $argumentList)) {
            return $argumentList;
        }

        $argumentValue = $argumentList[$this->argumentName];
        $hash = md5(serialize($argumentValue));

        // See if we have done something exactly like this already.
        if (!array_key_exists($hash, $this->_memoizedValues)) {
            $this->_memoizedValues[$hash] = $this->handleArgument($argumentValue);
        }

        $ids = $this->_memoizedValues[$hash];

        // Enforce no matches, if no matches. Doh.
        if (empty($ids)) {
            $ids = [0];
        }

        $relatedTo = $this->prepareRelatedTo($argumentList['relatedTo'] ?? []);

        if (empty($relatedTo)) {
            $relatedTo = ['and'];
        }

        $relatedTo[] = ['element' => $ids];

        $argumentList['relatedTo'] = $relatedTo;
        unset($argumentList[$this->argumentName]);

        return $argumentList;
    }

    /**
     * Prepare the `relatedTo` argument.
     * @param array $relatedTo
     * @return array
     */
    protected function prepareRelatedTo(array $relatedTo) : array
    {
        // Convert numeric arrays to ['and', ['element' => [...]]]

        if (empty($relatedTo)) {
            return [];
        }

        // If it begins with an "or", just drop the or.
        $firstOperand = StringHelper::toLowerCase($relatedTo[0]);
        if ($firstOperand === 'or' || $firstOperand === 'and') {
            array_shift($relatedTo);
        }

        if (ArrayHelper::isNumeric($relatedTo)) {
            $relatedTo = ['and', ['element' => $relatedTo]];
        } else {
            array_unshift($relatedTo, 'and');
        }

        return $relatedTo;
    }
}
