<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\AddressRepository;

class AddressController extends Controller
{
    protected $addressRepo;

	public function __construct(AddressRepository $addressRepo
    ) {
		$this->addressRepo = $addressRepo;
    }

    public function getAddress(Request $request)
    {
        // url encode the address
        $address = urlencode($request->input('address'));

        // google map geocode api url
        $url = "https://maps.googleapis.com/maps/api/geocode/json?address=".$address."&language=zh-tw&key=AIzaSyCW4-3YpHOVh4i-zn1glwTCjvHLeYvfli4";

        $data = file_get_contents($url);

        // decode the json
        $resp = json_decode($data, true);

        // response status will be 'OK', if able to geocode given address
        if($resp['status']=='OK'){

            // put the data in the array
            $data_arr = array();

            // get address data
            if(isset($resp['results'][0]['address_components']))
            {
                foreach($resp['results'][0]['address_components'] as $component)
                {
                    if(in_array("postal_code", $component['types']))
                        $data_arr['zip'] = $component['long_name'];
                    else if(in_array("administrative_area_level_1", $component['types']))
                        $data_arr['city'] = $component['long_name'];
                    else if(in_array("administrative_area_level_3", $component['types']))
                        $data_arr['area'] = $component['long_name'];
                    else if(in_array("route", $component['types']))
                    {
                        $route = $component['long_name'];
                        $splitRoutes = $this->parseRoute($route);
                        $data_arr['road'] = isset($splitRoutes['road']) ? $splitRoutes['road'] : "";
                        $data_arr['lane'] = isset($splitRoutes['lane']) ? $splitRoutes['lane'] : "";
                        $data_arr['alley'] = isset($splitRoutes['alley']) ? $splitRoutes['alley'] : "";
                        $data_arr['address'] = $splitRoutes['more'];
                    }
                    else if(in_array("street_number", $component['types']))
                        $data_arr['no'] = $component['long_name'];
                    else if(in_array("subpremise", $component['types']))
                        $data_arr['floor'] = $component['long_name'];
                }
            }
            $data_arr['latitude'] = isset($resp['results'][0]['geometry']['location']['lat']) ? $resp['results'][0]['geometry']['location']['lat'] : "";
            $data_arr['lontitue'] = isset($resp['results'][0]['geometry']['location']['lng']) ? $resp['results'][0]['geometry']['location']['lng'] : "";
            $data_arr['full_address'] = isset($resp['results'][0]['formatted_address']) ? $resp['results'][0]['formatted_address'] : "";

            //query json filename from DB
            if(isset($data_arr['area']))
                $data_arr['filename'] = $this->addressRepo->getFileNameByArea($data_arr['area']);

            return json_encode($data_arr);
        }

        return "無法解析地址";
    }

    private function parseRoute($route)
    {
        $arr = mb_str_split($route);
        $result = [];
        $len = count($arr);
        $tmpStr = "";

        //parse route one by one
        for($i = 0; $i < $len; $i++)
        {
            if($arr[$i] == '路' || $arr[$i] == '街')
            {
                $result['road'] = $tmpStr . $arr[$i];
                $tmpStr = "";
            }
            else if($arr[$i] == '段')
            {
                $result['road'] = $result['road'] . $tmpStr . $arr[$i];
                $tmpStr = "";
            }
            else if($arr[$i] == '巷')
            {
                $result['lane'] = $tmpStr;
                $tmpStr = "";
            }
            else if($arr[$i] == '弄')
            {
                $result['alley'] = $tmpStr;
                $tmpStr = "";
            }
            else
                $tmpStr = $tmpStr . $arr[$i];
        }
        $result['more'] = $tmpStr;

        return $result;
    }
}
