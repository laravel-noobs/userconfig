<?php namespace KouTsuneka\UserConfig;

use Symfony\Component\HttpFoundation\Request;

trait DefinesConfigs
{
    protected function load_configs()
    {
        $configs = [];
        foreach(array_keys($this->configs) as $key)
            $configs[$key] = UserConfig::get($this->config_key . '_' . $key);
        $this->configs = array_merge($this->configs, $configs);
    }

    protected function load_config($key)
    {
        $config = UserConfig::get($this->config_key . '_' . $key);
        if($config != null)
            $this->configs[$key] = array_merge($this->configs[$key], $config);
    }

    protected function save_config($key)
    {
        UserConfig::set($this->config_key . '_'. $key, $this->configs[$key]);
    }

    protected function save_configs()
    {
        foreach(array_keys($this->configs) as $key)
            UserConfig::set($this->config_key . '_' . $key, $this->configs[$key]);
    }

    protected function read_config($name, $reload = false)
    {
        $path = explode('.', $name);

        if($reload)
            $this->load_config($path[0]);

        $temp = $this->configs;
        foreach($path as $key)
        {
            if(!isset($temp[$key]))
                return null;
            $temp = $temp[$key];
        }
        return $temp;
    }

    protected function write_config($name, $value, $save = true)
    {
        $path = explode('.', $name);
        $temp = &$this->configs;
        foreach($path as $key) {
            if(!isset($temp[$key]))
                return;
            $temp = &$temp[$key];
        }
        $temp = $value;
        unset($temp);
        $this->save_config($path[0]);
    }

    public function postConfig(Request $request)
    {
        $input = $request->input();
        if(!isset($input['name']) || !isset($input['value']))
            throw new BadRequestHttpException();

        $this->write_config($input['name'], $input['value']);
    }
}