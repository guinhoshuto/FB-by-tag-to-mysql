<?php
require_once __DIR__ . '/vendor/autoload.php'; // change path as needed
require __DIR__ . '/config.php';

try {
    // Returns a `FacebookFacebookResponse` object
$response = $fb->get(
    '/407839822565162/posts?limit=100&fields=insights.metric(post_impressions,post_impressions_paid,post_impressions_unique,post_impressions_paid_unique,post_clicks_by_type,post_activity_by_action_type),created_time,message,id,type,permalink_url,full_picture',
    $token
);
} catch(FacebookExceptionsFacebookResponseException $e) {
    echo 'Graph returned an error: ' . $e->getMessage();
    exit;
    } catch(FacebookExceptionsFacebookSDKException $e) {
    echo 'Facebook SDK returned an error: ' . $e->getMessage();
    exit;
}
$teste = $response->getGraphEdge();

// $graphNode = $response->getGraphNode();
$posts = json_decode($teste);

foreach($posts as $post){
    $post->post_id = explode("_", $post->id);
    foreach($post->insights as $insights){
        switch($insights->name){
            case 'post_impressions':
                $post->impressions = $insights->values[0]->value;
                break;
            case 'post_impressions_paid':
                $post->paid_impressions = $insights->values[0]->value;
                break;
            case 'post_impressions_unique':
                $post->reach = $insights->values[0]->value;
                break;
            case 'post_impressions_paid_unique':
                $post->paid_reach = $insights->values[0]->value;
                break;
            case 'post_clicks_by_type':
                if(isset($insights->values[0]->value->{'other clicks'})){
                    $post->other_clicks = $insights->values[0]->value->{'other clicks'};
                } else {
                    $post->other_clicks = '0';
                }
                if(isset($insights->values[0]->value->{'photo view'})){
                    $post->photo_view = $insights->values[0]->value->{'photo view'};
                } else {
                    $post->photo_view = '0';
                } 
                if(isset($insights->values[0]->value->{'link clicks'})){
                    $post->link_clicks = $insights->values[0]->value->{'link clicks'};
                } else {
                    $post->link_clicks = '0';
                } 
                if(isset($insights->values[0]->value->{'video plays'})){
                    $post->video_plays = $insights->values[0]->value->{'video plays'};
                } else {
                    $post->video_plays = '0';
                } 
                break;
            case 'post_activity_by_action_type':
                if(isset($insights->values[0]->value->like)){
                    $post->likes = $insights->values[0]->value->like;
                } else {
                    $post->likes = '0';
                }
                if(isset($insights->values[0]->value->share)){
                    $post->share = $insights->values[0]->value->share;
                } else {
                    $post->share = '0';
                }
                if(isset($insights->values[0]->value->comment)){
                    $post->comments = $insights->values[0]->value->comment;
                } else {
                    $post->comments = '0';
                }
                break;
        }
    }


    $select = "SELECT id FROM `facebook-posts-insights` WHERE story_id ='" . $post->id . "'";
    echo $select;
    $publicacao = mysqli_query($conn, $select);
    echo '<pre>';
    print_r($post);
    if(mysqli_num_rows($publicacao) > 0 ){
        echo 'post registrado <br>'; 
        $update = "UPDATE `facebook-posts-insights` SET 
                    permalink='" . $post->permalink_url . "'," .
                    "full_picture='" . $post->full_picture . "'," .
                    "post_message='" . $post->message . "'," .
                    "type='" . $post->type . "'," . 
                    "posted='" . $post->created_time->date . "'," .
                    "lifetime_post_total_reach='" . $post->reach . "'," . 
                    "lifetime_post_paid_reach='" . $post->paid_reach . "'," . 
                    "lifetime_post_total_impressions='" . $post->impressions . "'," .
                    "lifetime_post_paid_impressions='" . $post->paid_impressions . "'," .
                    "likes='" . $post->likes . "'," .
                    "shares='" . $post->share . "'," .
                    "comments='" . $post->comments . "'," .
                    "video_plays='" . $post->video_plays . "'," .
                    "other_clicks='" . $post->other_clicks . "'," .
                    "photo_views='" . $post->photo_view . "'," .
                    "link_clicks='" . $post->link_clicks . "'," .
                    "updated_at='" . date("Y-m-d H:i:s") . 
                    "' WHERE story_id = '" . $post->id . "'";

        echo $update;
        if(mysqli_query($conn,$update)){
            $msg = "Atualizado com sucesso!";
        }else{
            $msg = "Erro ao atualizar!";
        }
        echo $msg;

    } else {
        $insert = "insert into `facebook-posts-insights` values('" 
                . $post->post_id[1] . "','"  
                . $post->id . "','"
                . $post->permalink_url . "','"
                . $post->full_picture . "','"
                . $post->message . "','"
                . $post->type . "','"
                . $post->created_time->date . "','"
                . $post->reach . "','"
                . $post->paid_reach . "','"
                . $post->impressions . "','"
                . $post->paid_impressions . "','"
                . $post->like . "','"
                . $post->comments . "','"
                . $post->shares . "','"
                . $post->video_plays . "','"
                . $post->other_clicks . "','"
                . $post->photo_view . "','"
                . $post->link_clicks . "','"
                . date("Y-m-d H:i:s") . "')";
                    
        echo $insert;
        if(mysqli_query($conn,$insert)){
            $msg = "Gravado com sucesso!";
        }else{
            $msg = "Erro ao gravar!";
        }
    }

    echo $post->full_picture;    
}

mysqli_close($conn);
echo '<pre>';
print_r($posts);
