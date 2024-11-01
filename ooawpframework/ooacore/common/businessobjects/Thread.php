<?php
/**
 * Base class for implementing a thread in a clean OO manner.  Threads simply extend this class
 * and implement the abstract process method.
 */
abstract class Thread {

    const THREAD_RUNNING = "RUNNING";
    const THREAD_EXITED = "EXITED";

    protected $pid;


    /**
     * Main run method called to start the thread.
     *
     */
    public function run($parameters = array()) {

        // Grab a process ID for a new forked process
        $this->pid = pcntl_fork();

        if (!$this->pid) {
            $this->process($parameters);
            posix_kill(getmypid(), 9);
        }

        return $this->pid;
    }


    /**
     * Blocking method called by another thread which waits until this
     * thread has completed.
     */
    public function wait() {
        if ($this->pid) {
            pcntl_waitpid($this->pid, $status, WUNTRACED);
            return $status;
        } else {
            return false;
        }
    }


    /**
     * Get the current status for this thread.
     */
    public function getStatus() {
        if ($this->pid) {
            $waitPID = pcntl_waitpid($this->pid, $status, WNOHANG);

            if ($waitPID == $this->pid) {
                return Thread::THREAD_EXITED;
            } else {
                return Thread::THREAD_RUNNING;
            }
        }
    }


    /**
     * Main process method which contains the sub logic for the thread.
     *
     * @return mixed
     */
    protected abstract function process($parameters);


}
