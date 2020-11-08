<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Repositories\AddressRepository;

class ImportController extends Controller
{
    protected $addressRepo;

	public function __construct(AddressRepository $addressRepo
    ) {
		$this->addressRepo = $addressRepo;
    }

    //import address data
    public function importAddress()
    {
        $resMsg = "資料匯入成功";
        $cityContents = file_get_contents(app_path() . "/imports/address/0/0.json");
        $cityData = json_decode($cityContents, true);

        foreach($cityData as $key => $value)
        {
            $city = [];
            DB::beginTransaction();
            try
            {
                $city['name'] = $value['city'];
                $cityId = $this->addressRepo->createCity($city);
                if($cityId)
                {
                    $area = [];
                    foreach($value['data'] as $key => $value)
                    {
                        $area['city_id'] = $cityId;
                        $area['name'] = $value['area'];
                        $area['zip'] = $value['zip'];
                        $area['filename'] = $value['filename'].".json";
                        $areaId = $this->addressRepo->createArea($area);
                        if($areaId)
                        {
                            $roads = [];
                            $roadsContents = file_get_contents(app_path() . "/imports/address/" . $value['filename'][0] . "/" . $area['filename']);
                            $roadsData = json_decode($roadsContents, true);
                            foreach($roadsData as $key => $value)
                            {
                                $roads[] = [
                                    'area_id' => $areaId,
                                    'name' => $value['name'],
                                    'abc' => $value['abc']
                                ];
                            }
                            $this->addressRepo->insertRoads($roads);
                        }
                    }
                }
                DB::commit();
            }
            catch(Exception $e)
            {
                DB::rollBack();
                $resMsg = "資料匯入失敗：" . $e->getMessage();
            }
        }

        echo $resMsg;
    }
}
