<?php

namespace RabbiteventsMod\Events\Console;

use Illuminate\Console\Command;
use Interop\Amqp\AmqpQueue;
use RabbiteventsMod\Events\Amqp\BindFactory;
use RabbiteventsMod\Events\Amqp\QueueFactory;
use RabbiteventsMod\Events\Console\Log;
use RabbiteventsMod\Events\Queue\Context;
use RabbiteventsMod\Events\Queue\Events\JobExceptionOccurred;
use RabbiteventsMod\Events\Queue\Events\JobFailed;
use RabbiteventsMod\Events\Queue\Events\JobProcessed;
use RabbiteventsMod\Events\Queue\Events\JobProcessing;
use RabbiteventsMod\Events\Queue\Jobs\Factory;
use RabbiteventsMod\Events\Queue\Message\Processor;
use RabbiteventsMod\Events\Queue\ProcessingOptions;
use RabbiteventsMod\Events\Queue\Manager;
use RabbiteventsMod\Events\Queue\Worker;

class ListenCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rabbitevents:listen
                            {event : The name of the event to listen to}
                            {--service= : The name of current service. Necessary to identify listeners}
                            {--connection= : The name of the queue connection to work}
                            {--memory=128 : The memory limit in megabytes}
                            {--timeout=60 : The number of seconds a child process can run}
                            {--tries=1 : Number of times to attempt a job before logging it failed}
                            {--sleep=5 : Sleep time in seconds before running failed job next time}
                            {--quiet: No console output}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Listen for event thrown from other services';

    /**
     * @var array
     */
    protected $logWriters = [];

    /**
     * Execute the console command.
     * @param Context $context
     * @param Worker $worker
     * @throws \Throwable
     */
    public function handle(Context $context, Worker $worker): void
    {

        $options = $this->gatherProcessingOptions();

        $this->registerLogWriters($options->connectionName);

        $this->listenForEvents();

        $queue = new Manager(
            $context->createConsumer($this->getQueue($context, $options)),
            $context->transport()
        );

        $worker->work(
            new Processor($this->laravel['events'], new Factory($this->laravel, $queue)),
            $queue,
            $options
        );
    }

    /**
     * Gather all of the queue worker options as a single object.
     *
     * @return ProcessingOptions
     */
    protected function gatherProcessingOptions(): ProcessingOptions
    {
        return new ProcessingOptions(
            $this->option('service') ?: $this->laravel['config']->get("app.name"),
            $this->option('connection') ?: $this->laravel['config']['rabbitevents.default'],
            $this->option('memory'),
            $this->option('timeout'),
            $this->option('tries'),
            $this->option('sleep')
        );
    }

    /**
     * Listen for the queue events in order to update the console output.
     *
     * @return void
     */
    protected function listenForEvents(): void
    {
        $callback = function ($event) {
            foreach ($this->logWriters as $writer) {
                $writer->log($event);
            }
        };

        $this->laravel['events']->listen(JobProcessing::class, $callback);
        $this->laravel['events']->listen(JobProcessed::class, $callback);
        $this->laravel['events']->listen(JobFailed::class, $callback);
        $this->laravel['events']->listen(JobExceptionOccurred::class, $callback);
    }

    /**
     * Register classes to write log output
     *
     * @param string $connection
     */
    protected function registerLogWriters(string $connection = 'rabbitmq'): void
    {
        if (!$this->option('quiet')) {
            $this->logWriters[] = new Log\Output($this->laravel, $this->output);
        }

        if ($this->laravel['config']->get("rabbitevents.connections.$connection.logging.enabled")) {
            $this->logWriters[] = new Log\General($this->laravel);
        }
    }

    /**
     * @param Context $context
     * @param ProcessingOptions $options
     * @return AmqpQueue
     */
    protected function getQueue(Context $context, ProcessingOptions $options): AmqpQueue
    {
        $factory = new QueueFactory(
            $context,
            new BindFactory($context),
            $options->service
        );

        return $factory->make($this->argument('event'));
    }
}
