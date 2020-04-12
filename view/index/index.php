<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>地图</title>
    <script type="text/javascript" src="https://api.map.baidu.com/api?v=1.0&type=webgl&ak=jWMAZ7jMYfp8r6QDV7q7iZx9lbrIuGc3"></script>
    <style type="text/css">
        html{height:100%}
        body{height:100%;margin:0px;padding:0px}
        #container{height:100%}
    </style>
</head>
<body>
    <div id="container"></div>
<script>
navigator.geolocation.getCurrentPosition(function (position) {

    var gps_latitude = position.coords.latitude;
    var gps_longitude = position.coords.longitude;

    var map = new BMapGL.Map("container");          // 创建地图实例
    var point = new BMapGL.Point(gps_longitude, gps_latitude);  // 创建点坐标

    var convertor = new BMapGL.Convertor();
    var pointArr = [];
    pointArr.push(point);
    convertor.translate(pointArr, 1, 5, function (data) {
        if(data.status === 0) {

            var bd_point = data.points[0];

            var marker = new BMapGL.Marker(bd_point);
            var label = new BMapGL.Label("当前在这里",{offset:new BMapGL.Size(20,-10)});
            marker.setLabel(label); //添加百度label

            map.addOverlay(marker);
            map.centerAndZoom(bd_point, 15); // 初始化地图，设置中心点坐标和地图级别
            map.enableScrollWheelZoom(true); // 开启鼠标滚轮缩放
        }
    });

});
</script>
</body>
</html>
