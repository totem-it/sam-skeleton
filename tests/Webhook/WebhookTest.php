<?php

declare(strict_types=1);

namespace Totem\SamSkeleton\Tests\Webhook;

use Illuminate\Foundation\Bus\PendingDispatch;
use Illuminate\Queue\Jobs\RedisJob;
use Illuminate\Support\Facades\Log;
use Laravel\Horizon\Events\JobDeleted;
use Orchestra\Testbench\TestCase;
use Psr\Log\LogLevel;
use Totem\SamSkeleton\Webhook\Webhook;

uses(TestCase::class);

beforeEach(function (): void {
    $this->webhook = new Webhook();
});

it('logs debug when job not failed', function () {
    Log::shouldReceive('log')
        ->once()
        ->with(
            LogLevel::DEBUG,
            'Webhook: [TestJob]',
            [
                'id' => 'test-job-id',
                'class' => 'TestJob',
                'body' => '',
            ]
        );

    $mock = $this->mock(RedisJob::class);
    $mock->shouldReceive('getJobId')->andReturn('test-job-id');
    $mock->shouldReceive('resolveName')->andReturn('TestJob');
    $mock->shouldReceive('hasFailed')->andReturn(false);
    $mock->shouldReceive('payload')->andReturn([
        'data' => ['command' => serialize((object) [])],
    ]);

    $event = new JobDeleted($mock, '');

    $this->webhook->handle($event);
});

it('logs error when job failed', function () {
    Log::shouldReceive('log')
        ->once()
        ->with(
            LogLevel::ERROR,
            'Webhook: [FailedJob]',
            [
                'id' => 'failed-job-id',
                'class' => 'FailedJob',
                'body' => '',
            ]
        );

    $mock = $this->mock(RedisJob::class);
    $mock->shouldReceive('getJobId')->andReturn('failed-job-id');
    $mock->shouldReceive('resolveName')->andReturn('FailedJob');
    $mock->shouldReceive('hasFailed')->andReturn(true);
    $mock->shouldReceive('payload')->andReturn([
        'data' => ['command' => serialize((object) [])],
    ]);

    $event = new JobDeleted($mock, '');

    $this->webhook->handle($event);
});

it('logs with command array when command has toArray method', function () {
    $commandData = ['key' => 'value', 'test' => 'data'];

    Log::shouldReceive('log')
        ->once()
        ->with(
            LogLevel::DEBUG,
            'Webhook: [TestJob]',
            [
                'id' => 'test-job-id',
                'class' => 'TestJob',
                'body' => $commandData,
            ]
        );

    $mock = $this->mock(RedisJob::class);
    $mock->shouldReceive('getJobId')->andReturn('test-job-id');
    $mock->shouldReceive('resolveName')->andReturn('TestJob');
    $mock->shouldReceive('hasFailed')->andReturn(false);
    $mock->shouldReceive('payload')->andReturn([
        'data' => ['command' => serialize(new FixtureJob($commandData))],
    ]);

    $event = new JobDeleted($mock, '');

    $this->webhook->handle($event);
});

it('works when command data is missing', function () {
    Log::shouldReceive('log')
        ->once()
        ->with(
            LogLevel::DEBUG,
            'Webhook: [TestJob]',
            [
                'id' => 'test-job-id',
                'class' => 'TestJob',
                'body' => '',
            ]
        );

    $mock = $this->mock(RedisJob::class);
    $mock->shouldReceive('getJobId')->andReturn('test-job-id');
    $mock->shouldReceive('resolveName')->andReturn('TestJob');
    $mock->shouldReceive('hasFailed')->andReturn(false);
    $mock->shouldReceive('payload')->andReturn([
        'data' => [],
    ]);

    $event = new JobDeleted($mock, '');

    $this->webhook->handle($event);
});

it('works when payload data is missing', function () {
    Log::shouldReceive('log')
        ->once()
        ->with(
            LogLevel::DEBUG,
            'Webhook: [TestJob]',
            [
                'id' => 'test-job-id',
                'class' => 'TestJob',
                'body' => '',
            ]
        );

    $mock = $this->mock(RedisJob::class);
    $mock->shouldReceive('getJobId')->andReturn('test-job-id');
    $mock->shouldReceive('resolveName')->andReturn('TestJob');
    $mock->shouldReceive('hasFailed')->andReturn(false);
    $mock->shouldReceive('payload')->andReturn([]);

    $event = new JobDeleted($mock, '');

    $this->webhook->handle($event);
});

it('run method returns pending dispatch', function () {
    $result = Webhook::run(new FixtureJob());

    expect($result)
        ->toBeInstanceOf(PendingDispatch::class);
});
