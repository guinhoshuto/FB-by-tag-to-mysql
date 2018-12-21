<?php
    require_once __DIR__ . '/vendor/autoload.php';
    require __DIR__ . '/config.php';

    $queryTag = "SELECT DISTINCT tag FROM `facebook-adsets` WHERE 1";

    $tagsCadastradas = mysqli_query($conn, $queryTag);
    if(mysqli_num_rows($tagsCadastradas) > 0){
        while($row = mysqli_fetch_assoc($tagsCadastradas)) {
            print_r( $row);
            try {
                $response = $fb->get(
                    $act_id . '/insights?fields=spend,reach,impressions,social_spend&date_preset=lifetime&filtering=[{"field":"campaign.adlabels","operator":"ANY","value":["' . $row['tag'] . '"]}]',
                    $token
                );
            } catch (FacebookExceptionsFacebookResponseException $e){
                echo 'Graph returned an error: ' . $e->getMessage();  
                exit; 
                } catch (FacebookExceptionsFacebookSDKException $e) {
                    echo 'Facebook SDK returned an error: ' . $e->getMessage();  
                    exit;
            }
            $fbObj = $response->getGraphEdge();
            $dadosTag = json_decode($fbObj);

            echo '<pre>';
            print_r($dadosTag);
            $verificaTag = "SELECT tag FROM `facebook-ads-tag` WHERE tag ='" . $row['tag'] . "'";
            echo $verificaTag;

            if(mysqli_num_rows(mysqli_query($conn, $verificaTag)) > 0 ){
                $updateTag = "UPDATE `facebook-ads-tag` SET
                              impressions='" . $dadosTag[0]->impressions . "',
                              reach='" . $dadosTag[0]->reach . "',
                              social_spend='" . $dadosTag[0]->social_spend . "',
                              spend='" . $dadosTag[0]->spend . "' WHERE tag='" . $row['tag'] . "'";
                if(mysqli_query($conn, $updateTag)){
                    $msg = "atualizado com sucesso";
                } else {
                    $msg = "erro ao atualizar";
                }
                echo $updateTag;

            } else {
                $insertTag = "INSERT into `facebook-ads-tag` values('" .
                              $row['tag'] . "', '" .  
                              $dadosTag[0]->impressions . "', '" . 
                              $dadosTag[0]->reach . "', '" .
                              $dadosTag[0]->social_spend . "', '" . 
                              $dadosTag[0]->spend . "')";
                echo $insertTag;
                if(mysqli_query($conn, $insertTag)){
                    $msg = "atualizado com sucesso";
                } else {
                    $msg = "erro ao atualizar";
                }
            }
        }
    }



    // $_mes = explode("-",$mes[0]->date_start);
    // $mesFormatado = $_mes[0] . $_mes[1] . "01";
    // $select = "SELECT mes FROM `facebook-ads-tag` WHERE mes='" . $mesFormatado . "'";  
    // echo $select;
    // echo '<pre>';
    // print_r($mes);

    // $_cadastrado = mysqli_query($conn,$select);
    // if(mysqli_num_rows($_cadastrado) > 0){
    //     $update = "UPDATE `facebook-ads-mensal` SET 
    //                alcance='" . $mes[0]->reach . 
    //                "', social_spend='" . $mes[0]->social_spend . 
    //                "', spend='" . $mes[0]->spend . 
    //                "', impressions='" .$mes[0]->impressions .
    //                "' WHERE mes='" . $mesFormatado . "'";
    //     if(mysqli_query($conn, $update)){
    //         $msg = "atualizado com sucesso";
    //     } else {
    //         $msg = "erro ao atualizar";
    //     }
    //     echo $msg;
    // } else {
    //     $insert = "INSERT into `facebook-ads-mensal` values('" . 
    //                $mesFormatado . "', '" .
    //                $mes[0]->reach . "', '" . 
    //                $mes[0]->social_spend . "', '" . 
    //                $mes[0]->spend . "', '" . 
    //                $mes[0]->impressions . "')";  
        
    //     echo $insert;
    //     if(mysqli_query($conn, $insert)){
    //         $msgi = 'gravado com sucesso';
    //     } else {
    //         $msgi = 'erro ao gravar';
    //     }
    //     echo $msgi;
    // }
    mysqli_close($conn);