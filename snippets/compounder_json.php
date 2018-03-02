<?php

if(!function_exists(isJson)){
    function isJson($string) {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }
}

if(!function_exists(getTemplateByName)){
    function getTemplateByName($name) {
        global $modx;
        $sql = "SELECT `id` FROM `modx_site_templates` WHERE `templatename` = '".trim($name)."' LIMIT 1";
        $query = $modx->prepare($sql);
        $query->execute();
        $result = $query->fetchAll(PDO::FETCH_ASSOC);

        $tpl_id = $result[0]['id'];
        return $tpl_id;
    }
}


if(!function_exists(getTemplateContent)){
    function getTemplateContent($id) {
        global $modx;
        $sql = "SELECT `content` FROM `modx_site_templates` WHERE id = ".$id." LIMIT 1";
        $query = $modx->prepare($sql);
        $query->execute();
        $result = $query->fetchAll(PDO::FETCH_ASSOC);

        $content = $result[0]['content'];
        return $content;
    }
}


$tvJSON = $modx->resource->getTVValue('section_factory');

$arrJSON = $modx->fromJSON($tvJSON);

$data_array = array();

//1
foreach($arrJSON as $v){
    $data_array[$v['MIGX_id']] = $v;
}

//2
foreach($data_array as $k => $v){

    foreach($v as $k2 => $v2){
        if(isJson($v2)){
            $v2_arr = $modx->fromJSON($v2);
            if(is_array($v2_arr)){
                unset($data_array[$k][$k2]);
                foreach($v2_arr as $v3){
                    //echo $k.'-'.$k2.'-'.$v3['MIGX_id'].' ';
                    $data_array[$k][$k2][$v3['MIGX_id']] = $v3;
                }
            }
        }
    }

}

//3
foreach($data_array as $k => $v){

    if(isset($v) && is_array($v)) foreach($v as $k2 => $v2){
        if(isset($v2) && is_array($v2)) foreach($v2 as $k3 => $v3){
            if(isset($v3) && is_array($v3)) foreach($v3 as $k4 => $v4){
                if(isJson($v4)){
                    $arr = $modx->fromJSON($v4);
                    if(is_array($arr)){
                        unset($data_array[$k][$k2][$k3][$k4]);
                        foreach($arr as $ar){
                            $data_array[$k][$k2][$k3][$k4][$ar['MIGX_id']] = $ar;
                        }
                    }
                }
            }
        }
    }

}
//

//4
foreach($data_array as $k => $v){

    if(isset($v) && is_array($v)) foreach($v as $k2 => $v2){
        if(isset($v2) && is_array($v2)) foreach($v2 as $k3 => $v3){
            if(isset($v3) && is_array($v3)) foreach($v3 as $k4 => $v4){
                if(isset($v4) && is_array($v4)) foreach($v4 as $k5 => $v5){
                    foreach($v5 as $k6 => $v6){
                        if(isJson($v6)){
                            $arr = $modx->fromJSON($v6);
                            if(is_array($arr)){
                                unset($data_array[$k][$k2][$k3][$k4][$k5][$k6]);
                                foreach($arr as $ar){
                                    $data_array[$k][$k2][$k3][$k4][$k5][$k6][$ar['MIGX_id']] = $ar;
                                }
                            }
                        }
                    }
                }

            }
        }
    }

}
//

$block_iteration = 0;
foreach($data_array as $migxid => $block){
    $block_iteration++;
    if($block['published'] == 1){
        if( ($block['ontop'] == 1 and $ontop == 1) or ($block['ontop'] != 1 and $ontop != 1) ){
            $tv_name = $block['MIGX_formname'];


            if(empty($block['section'])){$block['section'][0]['template'] = 0;} // show template if no data


            foreach($block['section'] as $section){


                if($section['template'] == 0){
                    $section['template'] = getTemplateByName($tv_name);
                }


                if($tv_name == 'flexible_template'){
                    //flexible
                    $content = $section['tpl'];
                    foreach($section['fields'] as $field){
                        if(!is_array($field['value'])){
                            $content = str_replace('(('.$field['field'].'))', $field['value'], $content);
                        }
                    }

                    //flexible
                }else{
                    $content = getTemplateContent($section['template']);
                    if($block['ontop'] == 1 and $ontop == 1){
                        $content = '<div class="block_id" data-id="'.$migxid.'">'.$content.'</div>';
                    }

                    $content = preg_replace_callback("/<migx1[^>]*>(.*?)<\\/migx1>/si", function($matches) use ($section) {

                        list($el_key, $el_template) = explode('||', $matches[1]);
                        $string = '';

                        if(isset($section[$el_key])){
                            $i = 0;
                            foreach($section[$el_key] as $k => $v){
                                $i++;
                                $tmp = $el_template;

                                if($i == 1){
                                    $tmp = str_replace('((+first+))', '1', $tmp);
                                }
                                $tmp = str_replace('((+iteration+))', $i, $tmp);
                                $tmp = str_replace('((+iteration0+))', $i-1, $tmp);

                                foreach($v as $k2 => $v2){
                                    $v2 = str_replace('src="assets', 'src="/assets', $v2);
                                    $v2 = str_replace('src="../assets', 'src="/assets', $v2);
                                    $tmp = str_replace('((+'.$k2.'+))', $v2, $tmp);
                                }
                                $string .= $tmp;

                                //migx2
                                $string = preg_replace_callback("/<migx2[^>]*>(.*?)<\\/migx2>/si", function($matches) use ($section, $el_key, $i) {

                                    list($el_key2, $el_template) = explode('|2|', $matches[1]);
                                    $string2 = '';

                                    $keys_array = array_keys($section[$el_key]);
                                    $key_value = $keys_array[$i-1];
                                    if(isset($section[$el_key][$key_value][$el_key2])){
                                        $j = 0;
                                        foreach($section[$el_key][$key_value][$el_key2] as $k => $v){
                                            $j++;
                                            $tmp = $el_template;
                                            if($j == 1){
                                                $tmp = str_replace('((++first++))', '1', $tmp);
                                            }
                                            $tmp = str_replace('((++iteration++))', $j, $tmp);
                                            foreach($v as $k2 => $v2){
                                                $v2 = str_replace('src="assets', 'src="/assets', $v2);
                                                $v2 = str_replace('src="../assets', 'src="/assets', $v2);
                                                $tmp = str_replace('((++'.$k2.'++))', $v2, $tmp);
                                            }
                                            $string2 .= $tmp;
                                        }
                                    }

                                    return $string2;
                                }, $string);
                                //migx2
                            }
                        }

                        return $string;
                    }, $content);

                    //get elements quantity from migx1 field
                    $content = preg_replace_callback("/<migx-count1[^>]*>(.*?)<\\/migx-count1>/si", function($matches) use ($section) {

                        $el_key = $matches[1];

                        $migx1_count = '';
                        if(isset($section[$el_key])){
                            $migx1_count = sizeof($section[$el_key]);
                        }

                        return $migx1_count;
                    }, $content);
                    //get elements quantity from migx1 field



                    foreach($section as $key => $el){
                        if(!is_array($el)){
                            $el = str_replace('src="assets', 'src="/assets', $el);
                            $el = str_replace('src="../assets', 'src="/assets', $el);
                            $content = str_replace('(('.$key.'))', $el, $content);
                        }
                    }
                }
                $content = str_replace('((iteration))', $block_iteration, $content);
                $content = str_replace('((iteration0))', $block_iteration-1, $content);
                print_r($content);

            }
        }elseif($ontop != 1){
            echo '<div class="content_block_id" data-id="'.$migxid.'"></div>';
        }
    }
}


$block = $data_array[1];

$block['template'] = '
	((title)) ((image)) ((price)) ((Advantage_list||))
';


foreach($data_array as $section ){
    $sql = "SELECT `content` FROM `modx_site_templates` WHERE id = ".$section['template']." LIMIT 1";
    $query = $modx->prepare($sql);
    $query->execute();
    $result = $query->fetchAll(PDO::FETCH_ASSOC);

    print_r($result[0]['content']);
}