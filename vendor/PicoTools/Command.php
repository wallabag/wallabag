<?php

/*
 * This file is part of picoTools.
 *
 * (c) Frédéric Guillot http://fredericguillot.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PicoTools;


/**
 * Execute an external command
 *
 * @author Frédéric Guillot
 */
class Command
{
    /**
     * Command line
     *
     * @access private
     * @var string
     */
    private $cmd_line = '';


    /**
     * Command stdout
     *
     * @access private
     * @var string
     */
    private $cmd_stdout = '';


    /**
     * Command stderr
     *
     * @access private
     * @var string
     */
    private $cmd_sdterr = '';


    /**
     * Command environements variables
     *
     * @access private
     * @var array
     */
    private $cmd_env = array();


    /**
     * Command working directory
     *
     * @access private
     * @var string
     */
    private $cmd_dir = null;


    /**
     * Command return value
     *
     * @access private
     * @var integer
     */
    private $cmd_return = 0;


    /**
     * Constructor
     *
     * @access public
     * @param string $command Command line
     */
    public function __construct($command)
    {
        $this->cmd_line = $command;
    }


    /**
     * Execute the command
     *
     * @access public
     */
    public function execute()
    {
        $process = proc_open(
            $this->cmd_line, 
            array(
                0 => array('pipe', 'r'),
                1 => array('pipe', 'w'),
                2 => array('pipe', 'w')
            ),
            $pipes,
            $this->cmd_dir,
            $this->cmd_env
        );

        if (is_resource($process)) {

            $this->cmd_stdout = stream_get_contents($pipes[1]);
            $this->cmd_stderr = stream_get_contents($pipes[2]);
            $this->cmd_return = proc_close($process);
        }
    }


    /**
     * Set working directory
     *
     * @access public
     * @param string $dir Working directory
     */
    public function setDir($dir)
    {
        $this->cmd_dir = $dir;
    }


    /**
     * Set command env variables
     *
     * @access public
     * @param array $env Environnement variables
     */
    public function setEnv(array $env)
    {
        $this->cmd_env = $env;
    }



    /**
     * Get the return value
     *
     * @access public
     * @return integer Return value
     */
    public function getReturnValue()
    {
        return $this->cmd_return;
    }


    /**
     * Get stdout
     *
     * @access public
     * @return string stdout
     */
    public function getStdout()
    {
        return $this->cmd_stdout;
    }


    /**
     * Get stderr
     *
     * @access public
     * @return string stderr
     */
    public function getStderr()
    {
        return $this->cmd_stderr;
    }
}