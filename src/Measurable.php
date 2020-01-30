<?php


namespace Workshop\Async;


interface Measurable
{
    /**
     * @return array<string> Key is the name of the metric, Value is the measure.
     */
    public function getMetrics(): array;
}