<?php declare(strict_types=1);

namespace App\Http\Controller;

use Swoft\Http\Server\Annotation\Mapping\Controller;
use Swoft\Http\Server\Annotation\Mapping\RequestMapping;
use Swoft\Http\Message\Request;


/**
 * @Controller()
 */
class IndexController
{
    /**
     * @RequestMapping(route="index")
     */
    public function index(Request $request)
    {
        $data = $request->get();
        var_dump($data);  // 在命令行输出
        return "注解路由1";
    }

    /**
     * @RequestMapping("/names[/{name}]")
     */
    public function hello(string $name)
    {
        return $name.'hhh';  // http://192.168.23.130:18308/names/hh  输出hhhhh
    }
  
    /**
     * @RequestMapping("/names2[/{uid}]")
     */
    public function hello2(int $uid)
    {
        return $uid.'hhh';  //  http://192.168.23.130:18308/names2/2
    }
  
    /**
     * @RequestMapping(route="hn[/{name}]")
     */
    public function namess(string $name)
    {
        return $name.'hhh'; // http://192.168.23.130:18308/index/hn/kk 输出 kkhhh
    }
  
    /**
     * @RequestMapping(route="bx/{name}")
     */
    public function bx(string $name)
    {
        return $name.'hhh'; // http://192.168.23.130:18308/index/bx/ll 输出 llhhh
    }
  
    /**
     * @RequestMapping(route="/res")
     * @param Request $request
     */
    public function res(Request $request)
    {
        $method = $request->getMethod();
        return $method;  // http://192.168.23.130:18308/res  输出GET
    }
  
    /**
     * @RequestMapping(route="/chatc[/{uid}]")
     */
    public function chats(int $uid)
    {
        $users = [
            1 => '程心',
            2 => '云天明',
        ];
        $receiveUid = $uid == 1 ? 2 : 1;
        $userName = $users[$uid];
        $data = compact('uid', 'userName', 'receiveUid');
        return view('index/chat', $data);
    }
}
