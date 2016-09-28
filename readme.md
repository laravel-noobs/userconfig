#userconfig


since laravel 5.3 not allow session to run in controller _constructor to walk around this use middle closure

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->load_config('test');
            return $next($request);
        });
    }
