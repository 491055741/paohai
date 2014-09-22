<?php
namespace Postcard\Libs;


class Maps
{
    const ACCESS_KEY = "502036fd52a42ba41df72226734725de";

    // Geocoding API
    const URL_GEOCODING = "http://api.map.baidu.com/geocoder/v2/";
    // Geo convert
    const URL_GEOCONV = "http://api.map.baidu.com/geoconv/v1/";

    
    /**
     * 根据坐标解析地址信息
     *
     * @param string $longitude
     * @param string $latitude
     * @param int $pois 0|1 是否获取指定位置周边的poi. 1时显示
     *          周边100米内的poi
     * @param string coordtype bd09ll(default)|gcj02ll|wgs84ll
     *         bd09ll: 百度经纬度坐标
     *         gcj02ll: 国测局经纬度坐标
     *         wgs84ll: GPS经纬度
     *
     * @return string format as:
     *      {
     *          status: 0,      // 0 is successful
     *          result: {
     *              location: {
     *                  lng: 116.32298703399,
     *                  lat: 39.983424051248,
     *              },
     *              formatted_address: '北京市海淀区中关村大街27号1101',
     *              business: '中关村，人民大学，苏州街',
     *              addressComponent: {
     *                  city: '北京市',
     *                  district: '海淀区',
     *                  province: '北京市',
     *                  street: '中关村大街',
     *                  street_number: '27号1101'
     *              },
     *              cityCode: 131
     *          }
     *      }
     *      
     *      {
     *          status: 5
     *          msg: 'AK Illegal or Not Exist',
     *          results: [],
     *      }
     */
    static public function geoLatLng2Address(
        $longitude, $latitude, $pois=0, $coordtype="bd09ll", $callback=''
    ) {
        $query = array(
            'ak' => self::ACCESS_KEY,
            'location' => $latitude . "," . $longitude,
            'coordtype' => $coordtype,
            'pois' => $pois,
            'output' => 'json',
            'callback' => $callback,
        );

        return HttpRequest::get(self::URL_GEOCODING, $query);
    }


    /**
     * 将非百度坐标转换为百度坐标
     *
     * @param string|array $coords source latlng
     *      lat,lng;lat,lng;lat,lng  or
     *      [[lng, lat], [lng, lat]]
     * @param int $from default 1
     *      1 -- GPS设备获取的角度坐标
     *      2 -- GPS获取的米制坐标，sogou地图所用坐标
     *      3 -- google, soso, aliyun, mapabc, amap地图所用坐标
     *      4 -- 3中列表地图坐标对应的米制坐标
     *      5 -- 百度地图采用的经纬度坐标 bd09ll
     *      6 -- 百度地图采用的米制坐标   db09mc
     *      7 -- mapbar地图坐标
     *      8 -- 51地图坐标
     * @param int $to default 5. valid value: 5|6
     *
     * @return string
     *      {
     *          status: 0,
     *          result: [
     *              {
     *                  x: 114.21892734521, // longitude
     *                  y: 29.575429778924, // latitude
     *              },
     *              ...
     *          ]
     *      }
     */
    static public function geoConv($coords, $from=3, $to=5, $callback='') {
        $coordsStrArray = '';
        if (is_array($coords)) {
            foreach ($coords as $coord) {
                $coordsStrArray[] = join(",", $coord);
            }
            $coords = join(";", $coordsStrArray);
        }

        $query = array(
            'ak' => self::ACCESS_KEY,
            'coords' => $coords,
            'from' => $from,
            'to' => $to,
            'output' => 'json',
            'callback' => $callback,
        );

        return HttpRequest::get(self::URL_GEOCONV, $query);
    }
}

/* End of file */
