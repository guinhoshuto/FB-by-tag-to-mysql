<?php
require_once __DIR__ . '/vendor/autoload.php'; // change path as needed
require __DIR__ . '/config.php';


try {
  // Returns a `FacebookFacebookResponse` object
  $response = $fb->get(
    'act_1457805817613704?fields=adsets.limit(100){name,lifetime_budget,budget_remaining,end_time,start_time,campaign{adlabels},id,campaign_id,account_id,created_time,configured_status,effective_status,status,ads{adcreatives{effective_object_story_id}}}',
    $token
  );
} catch(FacebookExceptionsFacebookResponseException $e) {
  echo 'Graph returned an error: ' . $e->getMessage();
  exit;
} catch(FacebookExceptionsFacebookSDKException $e) {
  echo 'Facebook SDK returned an error: ' . $e->getMessage();
  exit;
}
//Faz requisição dos anúncios
$graphNode = $response->getGraphNode();

$ads = json_decode($graphNode);
foreach($ads->adsets as $adsets){
    $adsets->tag = $adsets->campaign->adlabels[0]->name;
    //Verifica se post existe no bd
    $select = "select id FROM `facebook-adsets` WHERE id = '" . $adsets->id . "'";
    $post = mysqli_query($conn, $select);
    if(mysqli_num_rows($post) > 0 ){
        echo 'post registrado <br>'; 
        $update = "UPDATE `facebook-adsets` SET 
                    nome='" . $adsets->name . "'," .
                    "lifetime_budget='" . $adsets->lifetime_budget . "'," .
                    "budget_reimaning='" . $adsets->budget_remaining . "'," .
                    "end_time='" . $adsets->end_time->date . "'," . 
                    "start_time='" . $adsets->start_time->date . "'," .
                    "configured_status='" . $adsets->configured_status . "'," . 
                    "effective_stauts='" . $adsets->effective_status . "'," . 
                    "status='" . $adsets->status . "'," .
                    "story_id='" . $adsets->ads[0]->adcreatives[0]->effective_object_story_id . "'," .
                    "tag='" . $adsets->tag . "'," .
                    "updated_at='" . date("Y-m-d H:i:s") . 
                    "' WHERE id = '" . $adsets->id . "'";

        echo $update;
        if(mysqli_query($conn,$update)){
            $msg = "Atualizado com sucesso!";
        }else{
            $msg = "Erro ao atualizar!";
        }
        echo $msg;


    } else { 
        echo 'ainda não registrado <br>';
        $sql = "insert into `facebook-adsets` values('" 
            . $adsets->id . "','" 
            . $adsets->name . "','" 
            . $adsets->lifetime_budget . "','"
            . $adsets->budget_remaining . "','"
            . $adsets->end_time->date . "','"
            . $adsets->start_time->date . "','"
            . $adsets->campaign->id . "','"
            . $adsets->account_id . "','"
            . $adsets->created_time->date . "','"
            . $adsets->configured_status . "','"
            . $adsets->effective_status . "','"
            . $adsets->status . "','"
            . $adsets->ads[0]->adcreatives[0]->effective_object_story_id . "','"
            . $adsets->tag . "','"
            . date("Y-m-d H:i:s") . "')";
            
        if(mysqli_query($conn,$sql)){
            $msg = "Gravado com sucesso!";
        }else{
            $msg = "Erro ao gravar!";
        }
    }
    // echo $adsets->name;
}
mysqli_close($conn);    

echo '<pre>';
print_r($ads);