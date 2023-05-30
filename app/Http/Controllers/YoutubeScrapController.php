<?php

namespace App\Http\Controllers;

use Google\Client;
use Google\Exception;
use http\Url;
use Illuminate\Http\Request;

class YoutubeScrapController extends Controller
{
    public function index(): string
    {
//        $client = new Client();
//        $client->setAuthConfig('/keys/client_secret.json');
        return 'index..';
    }

    public function getGoogleClient(): \Google_Client
    {
        $autoloadPath = base_path('/vendor/autoload.php');
        $YOUTUBE_KEY = 'AIzaSyCjsZ7tAPGENzB3yfcxYXpP6iSmvRnkPOY';
        // 라이브러리 체크
        if (!file_exists($autoloadPath)) {
            throw new Exception('please run "composer require google/apiclient:~2.0" in "' . __DIR__ .'"');
        }

        require_once base_path('/vendor/autoload.php');
        $client = new \Google_Client();
        $client->setDeveloperKey($YOUTUBE_KEY);
        return $client;
    }

    /**
     * 어드민 -> youtubue url 입력 시 응답
     * @throws Exception
     */
    public function detail(Request $request): string
    {
        $returnObj = [
            'code' => '0000',
            'message' => 'success',
            'data' => null,
        ];

        $url = $request->get('url');
        $parsed = parse_url($url);
        $query = null;
        parse_str($parsed['query'], $query);
        $videoId = $query['v'];

        if (!$videoId) {
            $returnObj['code'] = '9999';
            $returnObj['message'] = '영상 정보를 조회할 수 없습니다.';
            return json_encode($returnObj);
        }

        session_start();
        $client = $this->getGoogleClient();
        $youtube =  new \Google_Service_YouTube($client);
        $listResponse = $youtube->videos->listVideos("snippet,contentDetails,statistics,player",
            array('id' => $videoId));
        if (empty($listResponse)) {
            $returnObj['code'] = '9999';
            $returnObj['message'] = '영상 정보를 조회할 수 없습니다.';
            return json_encode($returnObj);
        }

        $itemList = $listResponse->getItems();
        $item = $itemList[0];

        $snippet = $item->getSnippet();
        $statistics = $item->getStatistics();

        if (!$snippet || !$statistics) {
            $returnObj['code'] = '9999';
            $returnObj['message'] = '영상 정보를 조회할 수 없습니다.';
            return json_encode($returnObj);
        }

        // video info
        //게임타이틀(취합테이블기준) [미사용]
        //태그
        //영상 올린날짜
        //영상썸네일
        //영상제목
        //영상설명
        //영상조회수
        //좋아요
        $tags = $snippet->getTags();
        $publishedAt = $snippet->getPublishedAt();
        // 해상도 별 이미지 있음.
        $thumbnails = $snippet->getThumbnails()->getDefault();
        $title = $snippet->getTitle();
        $description = $snippet->getDescription();
        $viewCount = $statistics->getViewCount();
        $likeCount = $statistics->getLikeCount();

        $data = [
            'tags' => $tags,
            'publishedAt' => $publishedAt,
            'thumbnails' => $thumbnails,
            'title' => $title,
            'description' => $description,
            'viewCount' => $viewCount,
            'likeCount' => $likeCount
        ];

        // channel 정보
        //유튜버이름
        //유튜버구독자수
        //유튜버썸네일

        // channelId를 먼저 조회함.
        $channelId = $snippet->getChannelId();
        $channelInfo = $this->channelDetails($channelId);
        $data = array_merge($data, $channelInfo);
        $returnObj['data'] = $data;
        return json_encode($returnObj);
    }


    private function channelDetails($channelId)
    {

        $client = $this->getGoogleClient();
        $youtube =  new \Google_Service_YouTube($client);


        $listResponse = $youtube->channels->listChannels("snippet,statistics",
            array('id' => $channelId));

        if (empty($listResponse)) {
            throw new Exception('채널 정보를 조히할 수 없습니다.[1]');
        }

        $itemList = $listResponse->getItems();
        $item = $itemList[0];

        $snippet = $item->getSnippet();
        $statistics = $item->getStatistics();

        if (!$snippet || !$statistics) {
            throw new Exception('채널 정보를 조히할 수 없습니다.[2]');
        }

        $channelTitle = $snippet->getTitle();
        $channelThumbnails = $snippet->getThumbnails()->getDefault();
        $subscriberCount = $statistics->getSubscriberCount();

        return [
            'channelTitle' => $channelTitle,
            'channelThumbnails' => $channelThumbnails,
            'subscriberCount' => $subscriberCount
        ];
    }
}
