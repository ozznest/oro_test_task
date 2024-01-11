<?php

declare(strict_types=1);

namespace App\Formatter;

use Monolog\Formatter\LineFormatter;

class CustomLineFormatter extends LineFormatter
{
    public function __construct(string $format = null, string $dateFormat = null, bool $allowInlineLineBreaks = false, bool $ignoreEmptyContextAndExtra = false, bool $includeStacktraces = false)
    {
        parent::__construct($format, 'Y-m-d H:i:s', $allowInlineLineBreaks, $ignoreEmptyContextAndExtra, $includeStacktraces);
    }
}
