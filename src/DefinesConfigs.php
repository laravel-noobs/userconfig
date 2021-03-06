<?php namespace KouTsuneka\UserConfig;

use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Request;
use KouTsuneka\UserConfig\UserConfigFacade as UserConfig;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

trait DefinesConfigs
{
    protected function load_configs()
    {
        $configs = [];
        foreach(array_keys($this->configs) as $key)
        {
            $config = UserConfig::get($this->config_key . '_' . $key);
            if($config != null)
                $configs[$key] = $config;
        }

        $this->configs = array_merge($this->configs, $configs);
    }

    protected function load_config($key, $merge = true)
    {
        $config = UserConfig::get($this->config_key . '_' . $key);
        if($config != null)
            if($merge)
                $this->configs[$key] = array_merge($this->configs[$key], $config);
            else
                $this->configs[$key] = $config;
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
            $this->load_config($path[0], !$reload);

        $temp = $this->configs;
        foreach($path as $key)
        {
            if(!array_has($temp, $key))
                return null;
            $temp = $temp[$key];
        }
        return $temp;
    }

    protected function read_configs($names, $reload = false)
    {
        $ret = [];
        foreach($names as $name)
            $ret[str_replace('.', '_', $name)] = $this->read_config($name, $reload);
        return $ret;
    }

    protected function write_config($name, $value, $merge = false, $save = true)
    {
        $path = explode('.', $name);
        $temp = &$this->configs;
        foreach($path as $key) {
            if(!array_has($temp, $key))
                return;
            $temp = &$temp[$key];
        }
        if($merge)
            $temp = array_merge($temp, $value);
        else
            $temp = $value;

        unset($temp);
        $this->save_config($path[0]);
    }

    public function postConfig(Request $request)
    {
        $input = $request->input();
        if(!isset($input['name']) || !isset($input['value']))
            throw new BadRequestHttpException();

        if($input['value'] === "NULL")
            $input['value'] = null;


        if(isset($this->configs_validate[$input['name']]))
        {
            $validator = Validator::make([$input['name'] => $input['value']],[
                $input['name'] => $this->configs_validate[$input['name']]
            ]);
            if($validator->fails())
                return Response::json($validator->errors()->all(), 400);
        }

        $rules = [];
        foreach($this->configs_validate as $k => $v)
            if(substr($k, 0, strlen($input['name'] . '.')) == $input['name'] . '.')
                $rules[$k] = $v;

        if(!empty($rules))
        {
            $validator = Validator::make([$input['name'] => $input['value']], $rules);
            if($validator->fails())
                return Response::json($validator->errors()->all(), 400);
        }

        $this->write_config($input['name'], $input['value'], isset($input['merge']) ? $input['merge'] : false);
    }

    public function postConfigs(Request $request)
    {
        $input = $request->input();
        if(!isset($input['configs']))
            throw new BadRequestHttpException();
        foreach($input['configs'] as $config)
            if(!isset($config['name']) || !isset($config['value']))
                throw new BadRequestHttpException();

        foreach($input['configs'] as $config)
        {
            if($config['value'] === "NULL")
                $config['value'] = null;

            if(isset($this->configs_validate[$config['name']]))
            {
                $validator = Validator::make([$config['name'] => $config['value']],[
                    $config['name'] => $this->configs_validate[$config['name']]
                ]);
                if($validator->fails())
                    return Response::json($validator->errors()->all(), 400);
            }

            $rules = [];
            foreach($this->configs_validate as $k => $v)
                if(substr($k, 0, strlen($config['name'] . '.')) == $config['name'] . '.')
                    $rules[$k] = $v;

            if(!empty($rules))
            {
                $validator = Validator::make([$config['name'] => $config['value']], $rules);
                if($validator->fails())
                    return Response::json($validator->errors()->all(), 400);
            }

            $this->write_config($config['name'], $config['value']);
        }
    }
}