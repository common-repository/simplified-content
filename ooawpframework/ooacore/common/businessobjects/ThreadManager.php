<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Mark
 * Date: 05/06/13
 * Time: 09:28
 * To change this template use File | Settings | File Templates.
 */

/**
 *
 * Generic manager object which handles multiple threads - especially useful for running
 * threads with a maximum concurrency and also waiting on the completion of threads.
 *
 * Class ThreadManager
 */
class ThreadManager {

    private $threads;

    /**
     * Construct the thread manager optionally with an array of threads
     *
     * @param array $threads
     */
    public function ThreadManager($threads = array()) {
        $this->threads = $threads;
    }

    /**
     * Add a thread to this manager
     */
    public function addThread($thread) {
        $this->threads[] = $thread;
    }


    /**
     * Run all threads defined within this thread manager, enforcing the maximum concurrency
     * flag and optionally waiting for threads to complete.
     *
     * @param int $maximumConcurrency
     */
    public function runThreads($maximumConcurrency = 10, $waitForCompletion = false) {

        $remainingThreads = $this->threads;
        $activeThreads = array();

        do {

            // Check to see if threads have completed and remove these.
            foreach ($activeThreads as $pid => $thread) {
                if ($thread->getStatus() == Thread::THREAD_EXITED)
                    unset($activeThreads[$pid]);
            }


            // Start as many threads as we can.
            if ((sizeof($remainingThreads) > 0) && (sizeof($activeThreads) <= $maximumConcurrency)) {

                while ((sizeof($activeThreads) <= $maximumConcurrency) && (sizeof($remainingThreads) > 0)) {


                    $thread = array_shift($remainingThreads);
                    $pid = $thread->run();

                    // Store the pid
                    $activeThreads[$pid] = $thread;

                }
            }


            // If we have started the last thread and we are not waiting for completion,
            // exit this loop
            if (sizeof($remainingThreads) == 0 && !$waitForCompletion) {
                break;
            }


            // Wait a second before looping to avoid thrashing.
            sleep(1);


        } while (sizeof($activeThreads) > 0);


    }

}