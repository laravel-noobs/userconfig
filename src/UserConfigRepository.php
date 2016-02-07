<?php namespace KouTsuneka\UserConfig;

use \Illuminate\Session\SessionInterface;

class UserConfigRepository
{
    /**
     * The session store implementation.
     *
     * @var \Illuminate\Session\SessionInterface
     */
    protected $session;

    /**
     * @var string
     */
    protected $prefix_key = '_user';

    /**
     * Set the session store implementation.
     *
     * @param  \Illuminate\Session\SessionInterface $session
     *
     * @return $this
     */
    public function set_session_store(SessionInterface $session)
    {
        $this->session = $session;

        return $this;
    }

    /**
     *
     */
    public function __construct()
    {

    }

    public function get($key)
    {
        return $this->session->get($this->prefix_key . $key, null);
    }
    public function set($key, $value)
    {
        $this->session->set($this->prefix_key . $key, $value);
    }
}