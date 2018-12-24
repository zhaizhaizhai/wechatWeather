//weather.js //获取应用实例
var app = getApp() 
Page({
  data: {},
  //程序启动时运行 onload 
  onLoad: function () {
  //设置页面标题 
  wx.setNavigationBarTitle({
  title: '天气预报'
})
//console.log('onLoad')
var that = this
that.getLocation();//运行获取经纬度的方法 
},
//获取经纬度方法 
getLocation: function () {
var that = this
wx.getLocation({
  type: 'wgs84',
  success: function (res) {
    var latitude = res.latitude//获取纬度
    var longitude = res.longitude//获取经度
    //console.log("lat:" + latitude + " lon:" + longitude); 
    that.locationCorrect(latitude,longitude);//运行经纬度转换的方法
  }
})
},
//经纬度转换
locationCorrect: function (latitude, longitude) {
  var that = this
  var url = "https://api.map.baidu.com/geoconv/v1/";//访问 web 端百度 API 经纬度转换方法
  //访问页面所需要提供的参数 
  var params = {
  from: 1,
    to: 5,
    coords: longitude + "," + latitude, 
    ak: "GR8DB5pKQ70oaGiFS4m6qgVWeoPKQFPG" //百度网页端服务 AK
}
//调用微信访问 url 方法
wx.request({
  url: url,
  data: params,
  success: function (res) {
    //console.log(res);
    var badulat = res.data.result[0].y;
    var badulon = res.data.result[0].x; 
    that.getCity(badulat, badulon);//进入到经纬度转换城市方法
  },

  fail: function (res) { },
  complete: function (res) { },
})
},
//获取城市信息
getCity: function (latitude, longitude) {
  var that = this
  var url = "https://api.map.baidu.com/geocoder/v2/";//访问 web 端百度 API 逆经纬度转换方法
  var params = {
    ak: "GR8DB5pKQ70oaGiFS4m6qgVWeoPKQFPG",//百度网页端服务 AK
    output: "json",
    location: latitude + "," + longitude
  }
  wx.request({
    url: url,
    data: params,
    success: function (res) {
      var city = res.data.result.addressComponent.city;//城市信息
      var district = res.data.result.addressComponent.district;//区级信息
      var street = res.data.result.addressComponent.street;//街道信息 
      //渲染到 wxml 页面中
      that.setData({
        city: city, 
        district: district, 
        street: street,
      })
      var descCity = city.substring(0, city.length - 1);//去掉城市信息 最后的字符
      that.getWeahter(descCity);//进入获取天气信息方法 
      },
      fail: function (res) { },
      complete: function (res) { },
    })
},
//获取天气信息
getWeahter: function (city) {
  var that = this
  var url = "https://api.map.baidu.com/telematics/v3/weather"//访问百度 API 微信小程序端获取天气方法
  var params = {
    location: city,
    output: "json",
    ak: "iZIUDcKwA2CxsPebfciUxlM4gcGV1OLi"//百度微信小程序AK 
    }
wx.request({
      url: url,
      data: params,
      success: function (res) {
        console.log(res);
        var tmp = res.data.results[0].weather_data[0].temperature;//当天 温度
        var weather = res.data.results[0].weather_data[0].weather;//天气 情况
        var date = res.data.date;//当天日期
        var curdata = res.data.results[0].weather_data[0].date;//实时日期 温度
        var curdate = curdata.substring(0, curdata.indexOf('('));//从实时 日期温度取出日期
        var curtmp = curdata.substring(curdata.indexOf(':') + 15, curdata.length - 1)//取出温度
        var pm25 = res.data.results[0].pm25;//PM2.5
        var wind = res.data.results[0].weather_data[0].wind;//风向强弱
        var pic1 = res.data.results[0].weather_data[0].dayPictureUrl;//天气图片
        var pic2 = res.data.results[0].weather_data[0].nightPictureUrl;//天气图片
        var lifeindex = res.data.results[0].index;//生活指数
        var cast = res.data.results[0].weather_data;//近几天天气预报
        var forecast = cast.slice(1, cast.length);//近几天天气预报过滤掉今天
        that.setData({//渲染到 wxml 页面 date: date,
          tmp: tmp,
          weather: weather,
          curdata: curdata,
          curtmp: curtmp,
          curdate: curdate,
          pm25: pm25,
          wind: wind,
          pic1: pic1,//变量 pic 内存储的是图片 url pic2: pic2,
          lifeindex: lifeindex,
          forecast: forecast
        })
        //将变量存到临时数据存储器中

        wx.setStorage({
          key: 'life',//标签名 data: lifeindex,//数据
        })
      },
      fail: function (res) { },
      complete: function (res) { },
    })
  }
})