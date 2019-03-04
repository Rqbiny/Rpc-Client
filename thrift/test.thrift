namespace php app.Rpc.Thrift  # 指定生成什么语言，生成文件存放的目录

// 返回结构体
struct Response {
    1: i32 code;    // 返回状态码
    2: string msg;  // 码字回提示语名
    3: string data; // 返回内容
}

// 服务体
service TestService {
    // json 字符串参数  客户端请求方法
    Response invokeMethod(1:string params)
}
