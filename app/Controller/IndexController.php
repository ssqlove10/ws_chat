<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace App\Controller;

use Hyperf\DbConnection\Db;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Redis\Redis;
use Hyperf\Utils\ApplicationContext;

/**
 * Class IndexController
 * @package App\Controller
 * @Controller()
 */
class IndexController extends AbstractController
{
    public function index()
    {
        $user = $this->request->input('user', 'Hyperf');
        $method = $this->request->getMethod();

        return [
            'method' => $method,
            'message' => "Hello {$user}.",
        ];
    }

    /**
     * @RequestMapping(path="/test",methods="get,post")
     * @return array
     */
    public function test()
    {
        $test = Db::select("select * from test;");
        var_dump($test);
        $container = ApplicationContext::getContainer();
        $redis = $container->get(\Redis::class);
        $request = $container->get(RequestInterface::class);
        var_dump($request->fullUrl());
        $redis->set('tst',1);
        var_dump($redis->get('tst'));
        return ['test' =>1];
    }
}
