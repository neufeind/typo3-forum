<?php
namespace Mittwald\Typo3Forum\Controller;

/*                                                                      *
 *  COPYRIGHT NOTICE                                                    *
 *                                                                      *
 *  (c) 2013 Martin Helmich <m.helmich@mittwald.de>                     *
 *           Sebastian Gieselmann <s.gieselmann@mittwald.de>            *
 *           Ruven Fehling <r.fehling@mittwald.de>                      *
 *           Mittwald CM Service GmbH & Co KG                           *
 *           All rights reserved                                        *
 *                                                                      *
 *  This script is part of the TYPO3 project. The TYPO3 project is      *
 *  free software; you can redistribute it and/or modify                *
 *  it under the terms of the GNU General Public License as published   *
 *  by the Free Software Foundation; either version 2 of the License,   *
 *  or (at your option) any later version.                              *
 *                                                                      *
 *  The GNU General Public License can be found at                      *
 *  http://www.gnu.org/copyleft/gpl.html.                               *
 *                                                                      *
 *  This script is distributed in the hope that it will be useful,      *
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of      *
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the       *
 *  GNU General Public License for more details.                        *
 *                                                                      *
 *  This copyright notice MUST APPEAR in all copies of the script!      *
 *                                                                      */

use Mittwald\Typo3Forum\Domain\Model\Forum\Post;

class AjaxController extends AbstractController {

	/**
	 * @var \Mittwald\Typo3Forum\Domain\Repository\Forum\AdsRepository
	 * @inject
	 */
	protected $adsRepository;

	/**
	 * @var \Mittwald\Typo3Forum\Domain\Repository\Forum\AttachmentRepository
	 * @inject
	 */
	protected $attachmentRepository;

	/**
	 * @var \Mittwald\Typo3Forum\Service\AttachmentService
	 * @inject
	 */
	protected $attachmentService = NULL;

	/**
	 * @var \Mittwald\Typo3Forum\Domain\Repository\Forum\ForumRepository
	 * @inject
	 */
	protected $forumRepository;

	/**
	 * @var \TYPO3\CMS\Extbase\Configuration\FrontendConfigurationManager
	 * @inject
	 */
	protected $frontendConfigurationManager;

	/**
	 * @var \Mittwald\Typo3Forum\Domain\Factory\Forum\PostFactory
	 * @inject
	 */
	protected $postFactory;

	/**
	 * @var \Mittwald\Typo3Forum\Domain\Repository\Forum\postRepository
	 * @inject
	 */
	protected $postRepository;

	/**
	 * Whole TypoScript typo3_forum settings
	 * @var array
	 */
	protected $settings;

	/**
	 * @var \Mittwald\Typo3Forum\Domain\Repository\Forum\TopicRepository
	 * @inject
	 */
	protected $topicRepository;

	/**
	 * @var \TYPO3\CMS\Extbase\Service\TypoScriptService
	 * @inject
	 */
	protected $typoScriptService = NULL;

	/**
	 *
	 */
	public function initializeObject() {
		$ts = $this->typoScriptService->convertTypoScriptArrayToPlainArray($this->frontendConfigurationManager->getTypoScriptSetup());
		$this->settings = $ts['plugin']['tx_typo3forum']['settings'];
	}

	/**
	 * @param string $displayedUser
	 * @param string $postSummarys
	 * @param string $topicIcons
	 * @param string $forumIcons
	 * @param string $displayedTopics
	 * @param int $displayOnlinebox
	 * @param string $displayedPosts
	 * @param string $displayedForumMenus
	 * @param string $displayedAds
	 * @return void
	 */
	public function mainAction($displayedUser = "", $postSummarys = "", $topicIcons = "", $forumIcons = "", $displayedTopics = "", $displayOnlinebox = 0, $displayedPosts = "", $displayedForumMenus = "", $displayedAds = "") {
		// json array
		$content = array();
		if (!empty($displayedUser)) {
			$content['onlineUser'] = $this->_getOnlineUser($displayedUser);
		}
		if (!empty($displayedForumMenus)) {
			$content['forumMenus'] = $this->_getForumMenus($displayedForumMenus);
		}
		if (!empty($postSummarys)) {
			$content['postSummarys'] = $this->_getPostSummarys($postSummarys);
		}
		if (!empty($topicIcons)) {
			$content['topicIcons'] = $this->_getTopicIcons($topicIcons);
		}
		if (!empty($forumIcons)) {
			$content['forumIcons'] = $this->_getForumIcons($forumIcons);
		}
		if (!empty($displayedTopics)) {
			$content['topics'] = $this->_getTopics($displayedTopics);
		}
		if (!empty($displayedPosts)) {
			$content['posts'] = $this->_getPosts($displayedPosts);
		}
		if (!empty($displayedPosts)) {
			$content['posts'] = $this->_getPosts($displayedPosts);
		}
		if($displayOnlinebox == 1){
			$content['onlineBox'] = $this->_getOnlinebox();
		}
		$displayedAds = json_decode($displayedAds);
		if((int)$displayedAds->count > 1) {
			$content['ads'] = $this->_getAds($displayedAds);
		}

		$this->view->assign('content', json_encode($content));
	}


	/**
	 * @return void
	 */
	public function loginboxAction(){
		$this->view->assign('user', $this->getCurrentUser());
	}

	/**
	 * @return array
	 */
	private function _getOnlinebox(){
		$data = array();
		$data['count'] = $this->frontendUserRepository->countByFilter(TRUE);
		$this->request->setFormat('html');
		$users = $this->frontendUserRepository->findByFilter((int)$this->settings['widgets']['onlinebox']['limit'], array(), TRUE);
		$this->view->assign('users', $users);
		$data['html'] = $this->view->render('Onlinebox');
		$this->request->setFormat('json');
		return $data;
	}
	/**
	 * @param string $displayedForumMenus
	 * @return array
	 */
	private function _getForumMenus($displayedForumMenus){
		$data = array();
		$displayedForumMenus = json_decode($displayedForumMenus);
		if(count($displayedForumMenus) < 1) return $data;
		$this->request->setFormat('html');
		$foren = $this->forumRepository->findByUids($displayedForumMenus);
		$counter = 0;
		foreach($foren as $forum){
			$this->view->assign('forum', $forum)
				->assign('user', $this->getCurrentUser());
			$data[$counter]['uid'] = $forum->getUid();
			$data[$counter]['html'] = $this->view->render('ForumMenu');
			$counter ++;
		}
		$this->request->setFormat('json');
		return $data;
	}
	/**
	 * @param string $displayedPosts
	 * @return array
	 */
	private function _getPosts($displayedPosts){
		$data = array();
		$displayedPosts = json_decode($displayedPosts);
		if(count($displayedPosts) < 1) return $data;
		$this->request->setFormat('html');
		$posts = $this->postRepository->findByUids($displayedPosts);
		$counter = 0;
		foreach($posts as $post){
			/** @var Post $post */
			$this->view->assign('post', $post)
			->assign('user', $this->getCurrentUser());
			$data[$counter]['uid'] = $post->getUid();
			$data[$counter]['postHelpfulButton'] = $this->view->render('PostHelpfulButton');
			$data[$counter]['postHelpfulCount'] = $post->getHelpfulCount();
			$data[$counter]['postUserHelpfulCount'] = $post->getAuthor()->getHelpfulCount();
			$data[$counter]['author']['uid'] = $post->getAuthor()->getUid();
			$data[$counter]['postEditLink'] = $this->view->render('PostEditLink');
			$counter ++;
		}
		$this->request->setFormat('json');
		return $data;
	}
	/**
	 * @param string $displayedTopics
	 * @return array
	 */
	private function _getTopics($displayedTopics){
		$data = array();
		$displayedTopics = json_decode($displayedTopics);
		if(count($displayedTopics) < 1) return $data;
		$this->request->setFormat('html');
		$topicIcons = $this->topicRepository->findByUids($displayedTopics);
		$counter = 0;
		foreach($topicIcons as $topic){
			$this->view->assign('topic', $topic);
			$data[$counter]['uid'] = $topic->getUid();
			$data[$counter]['replyCount'] = $topic->getReplyCount();
			$data[$counter]['topicListMenu'] = $this->view->render('topicListMenu');
			$counter ++;
		}
		$this->request->setFormat('json');
		return $data;
	}

	/**
	 * @param string $topicIcons
	 * @return array
	 */
	private function _getTopicIcons($topicIcons){
		$data = array();
		$topicIcons = json_decode($topicIcons);
		if(count($topicIcons) < 1) return $data;
			$this->request->setFormat('html');
		$topicIcons = $this->topicRepository->findByUids($topicIcons);
		$counter = 0;
		foreach($topicIcons as $topic){
			$this->view->assign('topic', $topic);
			$data[$counter]['html'] = $this->view->render('topicIcon');
			$data[$counter]['uid'] = $topic->getUid();
			$counter ++;
		}
		$this->request->setFormat('json');
		return $data;
	}

	/**
	 * @param string $forumIcons
	 * @return array
	 */
	private function _getForumIcons($forumIcons){
		$data = array();
		$forumIcons = json_decode($forumIcons);
		if(count($forumIcons) < 1) return $data;
		$this->request->setFormat('html');
		$forumIcons = $this->forumRepository->findByUids($forumIcons);
		$counter = 0;
		foreach($forumIcons as $forum){
			$this->view->assign('forum', $forum);
			$data[$counter]['html'] = $this->view->render('forumIcon');
			$data[$counter]['uid'] = $forum->getUid();
			$counter ++;
		}
		$this->request->setFormat('json');
		return $data;
	}

	/**
	 * @param string $postSummarys
	 * @return array
	 */
	private function _getPostSummarys($postSummarys) {
		$postSummarys = json_decode($postSummarys);
		$data = array();
		$counter = 0;
		$this->request->setFormat('html');
		foreach($postSummarys as $summary){
			$post = false;
			switch($summary->type){
				case 'lastForumPost':
					$forum  = $this->forumRepository->findByUid($summary->uid);
					/* @var Post */
					$post = $forum->getLastPost();
					break;
				case 'lastTopicPost':
					$topic  = $this->topicRepository->findByUid($summary->uid);
					/* @var Post */
					$post = $topic->getLastPost();
					break;
			}
			if($post){
				$data[$counter] = $summary;
				$this->view->assign('post', $post)
							->assign('hiddenImage', $summary->hiddenimage);
				$data[$counter]->html = $this->view->render('postSummary');
				$counter ++;
			}
		}
		$this->request->setFormat('json');
		return $data;
	}

	/**
	 * @param array $displayedUser
	 * @return array
	 */
	private function _getOnlineUser($displayedUser) {
		// OnlineUser
		$displayedUser = json_decode($displayedUser);
		$onlineUsers = $this->frontendUserRepository->findByFilter("", array(), true, $displayedUser);
		// write online user
		foreach ($onlineUsers as $onlineUser) {
			$output[] = $onlineUser->getUid();
		}
		if (!empty($output)) return $output;
	}


	/**
	 * @param \stdClass $meta
	 * @return array
	 */
	private function _getAds(\stdClass $meta){
		$count = (int)$meta->count;
		$result = array();
		$this->request->setFormat('html');

		$actDatetime = new \DateTime();
		if(!$this->sessionHandling->get('adTime')) {
			$this->sessionHandling->set('adTime', $actDatetime);
			$adDateTime = $actDatetime;
		} else {
			$adDateTime = $this->sessionHandling->get('adTime');
		}
		if($actDatetime->getTimestamp() - $adDateTime->getTimestamp() > $this->settings['ads']['timeInterval'] && $count > 2){
			$this->sessionHandling->set('adTime', $actDatetime);
			if((int)$meta->mode === 0) {
				$ads = $this->adsRepository->findForForumView(1);
			} else {
				$ads = $this->adsRepository->findForTopicView(1);
			}
			if(!empty($ads)) {
				$this->view->assign('ads', $ads);
				$result['html'] = $this->view->render('ads');
				$result['position'] = mt_rand(1,$count-2);
			}
		}
		$this->request->setFormat('json');
		return $result;
	}

}
