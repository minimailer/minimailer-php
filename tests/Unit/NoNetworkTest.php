<?php

declare(strict_types=1);

/*
 * Tripwire: no test may ever touch the real MiniMailer server. Every Guzzle
 * client constructed anywhere under tests/ must be wired to a mock handler;
 * a `new GuzzleClient` without a `handler` option would go live on first use.
 */
it('never constructs an unmocked Guzzle client anywhere in the test suite', function (): void {
    $files = array_merge(
        glob(__DIR__.'/*.php') ?: [],
        glob(dirname(__DIR__).'/*.php') ?: [],
    );

    expect($files)->not->toBeEmpty();

    foreach ($files as $file) {
        $source = (string) file_get_contents($file);

        preg_match_all('/new\s+GuzzleClient\s*(\(([^;]*?)\))?\s*;/s', $source, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            expect(str_contains($match[2] ?? '', 'handler'))
                ->toBeTrue($file.' constructs a Guzzle client without a mock handler');
        }
    }
});
