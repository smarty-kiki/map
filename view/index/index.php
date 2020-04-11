<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>地图</title>
    <script type="text/javascript" src="http://api.map.baidu.com/api?v=1.0&type=webgl&ak=jWMAZ7jMYfp8r6QDV7q7iZx9lbrIuGc3"></script>
    <style type="text/css">
        html{height:100%}
        body{height:100%;margin:0px;padding:0px}
        #container{height:100%}
    </style>
</head>
<body>
    <div id="container"></div>
<script>
    var map = new BMapGL.Map("container");          // 创建地图实例
    var point = new BMapGL.Point(116.404, 39.915);  // 创建点坐标
    map.centerAndZoom(point, 15);                 // 初始化地图，设置中心点坐标和地图级别
    map.enableScrollWheelZoom(true);     //开启鼠标滚轮缩放
</script>
</body>
</html>
