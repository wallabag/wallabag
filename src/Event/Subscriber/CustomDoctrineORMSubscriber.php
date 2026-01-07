<?php

namespace Wallabag\Event\Subscriber;

use Spiriit\Bundle\FormFilterBundle\Event\GetFilterConditionEvent;
use Spiriit\Bundle\FormFilterBundle\Event\Subscriber\DoctrineORMSubscriber;
use Spiriit\Bundle\FormFilterBundle\Filter\Doctrine\ORMQuery;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * This custom class override the default behavior of SpiriitFormFilterBundle on `filter_date_range`
 * It converts a date_range to date_time_range to add hour to be able to grab a whole day (from 00:00:00 to 23:59:59).
 */
class CustomDoctrineORMSubscriber extends DoctrineORMSubscriber implements EventSubscriberInterface
{
    public function filterDateRange(GetFilterConditionEvent $event)
    {
        $filterQuery = $event->getFilterQuery();

        \assert($filterQuery instanceof ORMQuery);

        $expr = $filterQuery->getExpressionBuilder();
        $values = $event->getValues();
        $value = $values['value'];

        // left date should start at midnight
        if (isset($value['left_date'][0]) && $value['left_date'][0] instanceof \DateTime) {
            $value['left_date'][0]->setTime(0, 0, 0);
        }

        // right adte should end one second before midnight
        if (isset($value['right_date'][0]) && $value['right_date'][0] instanceof \DateTime) {
            $value['right_date'][0]->setTime(23, 59, 59);
        }

        if (isset($value['left_date'][0]) || isset($value['right_date'][0])) {
            $event->setCondition($expr->dateTimeInRange($event->getField(), $value['left_date'][0], $value['right_date'][0]));
        }
    }
}
