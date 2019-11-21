<?php

namespace app\admin\controller;

use app\common\lib\Upload;
use app\common\model\Category;
use app\common\model\VideoSection;
use think\Controller;
use think\Request;
use think\Validate;

class Video extends Base
{

    public function index()
    {
        $list = (new \app\common\model\Video())->where(['delete_time'=>0])->order('id', 'desc')->paginate(15);

        $this->assign('list', $list);

        return $this->fetch();
    }

    public function add()
    {
        //分类
        $type = new \app\common\typeCode\cate\Video();
        $cate = (new Category())->getList($type);

        //作者
        $author = (new \app\common\model\Teacher())->order('id', 'desc')->select()->toArray();

        $this->assign('cate', $cate);

        $this->assign('author', $author);

        return $this->fetch();
    }

    public function save(Request $request)
    {
        $post = $request->post();

        $rules = [
            'cate_id|分类' => 'require',
            'author_id|作者' => 'number',
            'title|标题' => 'require|max:60',
            'pic|封面' => 'require',
            'desc|介绍' => 'require',
            'roll_pic|轮播图' => 'require',
            'price|售价' => 'require',
        ];

        $validate = new Validate($rules);

        if (!$validate->check($post)) {
            return json(['code' => 0, 'msg' => $validate->getError()]);
        }

        $videoModel = new \app\common\model\Video();

        $videoModel->startTrans();
        try {

            $video = [
                'cate_id' => $post['cate_id'],
                'author_id' => $post['author_id'],
                'title' => $post['title'],
                'pic' => $post['pic'],
                'desc' => $post['desc'],
                'roll_pic' => $post['roll_pic'],
                'price' => $post['price'],
                'section_sum' => count($post['chapter_num']),
                'create_time' => time(),
            ];

            $videoModel->insert($video);

            $newId = $videoModel->getLastInsID();

            $chapter = [];

            foreach ($post['chapter_num'] as $key => $value) {
                $chapter[] = [
                    'video_id' => $newId,
                    'number' => $value,
                    'title' => $post['chapter_title'][$key],
                    'source_url' => $post['chapter_video'][$key],
                ];
            }

            (new VideoSection())->insertAll($chapter);
            $videoModel->commit();
        } catch (\Exception $e) {
            $videoModel->rollback();
            return json(['code' => 0, 'msg' => $e->getMessage()]);
        }

        return json(['code' => 1, 'msg' => 'success']);
    }

    public function edit(Request $request)
    {
        $id = $request->param('id');

        $data = (new \app\common\model\Video())->find($id);

        $section = (new VideoSection())->where(['video_id' => $id])->order('number')->select()->toArray();

        //分类
        $type = new \app\common\typeCode\cate\Video();
        $cate = (new Category())->getList($type);

        //作者
        $author = (new \app\common\model\Teacher())->order('id', 'desc')->select()->toArray();

        $this->assign('cate', $cate);

        $this->assign('author', $author);

        $this->assign('data', $data);

        $this->assign('section', $section);

        return $this->fetch();
    }

    public function update(Request $request)
    {
        $post = $request->post();

        $rules = [
            'id' => 'require',
            'cate_id|分类' => 'require',
            'author_id|作者' => 'number',
            'title|标题' => 'require|max:60',
            'pic|封面' => 'require',
            'desc|介绍' => 'require',
            'roll_pic|轮播图' => 'require',
            'price|售价' => 'require',
        ];

        $validate = new Validate($rules);

        if (!$validate->check($post)) {
            return json(['code' => 0, 'msg' => $validate->getError()]);
        }

        $videoModel = new \app\common\model\Video();

        $videoModel->startTrans();
        try {
             $sectionSum = 0;
            isset($post['old_chapter_num']) && $sectionSum = count($post['old_chapter_num']) ?? 0;
             isset($post['chapter_num']) && $sectionSum += count($post['chapter_num']) ?? 0;
            $video = [
                'cate_id' => $post['cate_id'],
                'author_id' => $post['author_id'],
                'title' => $post['title'],
                'pic' => $post['pic'],
                'desc' => $post['desc'],
                'roll_pic' => $post['roll_pic'],
                'price' => $post['price'],
                'section_sum' => $sectionSum,
                'create_time' => time(),
            ];

            $videoModel->where(['id' => $post['id']])->update($video);


            $sectionModel = new VideoSection();

            //循环对老数组进行操作 如果存在
            if (isset($post['old_chapter_num'])) {
                $oldIds = [];
                foreach ($post['old_chapter_id'] as $key => $value) {
                    $oldIds[] = $value;
                    $newData = [
                        'number' => $post['old_chapter_num'][$key],
                        'title' => $post['old_chapter_title'][$key],
                        'source_url' => $post['old_chapter_video'][$key],
                    ];
                    $sectionModel->where(['id' => $value])->update($newData);
                }
                $sectionModel->where(['video_id' => $post['id']])->whereNotIn('id', $oldIds)->delete();
            }

            if (isset($post['chapter_num'])) {
                $chapter = [];

                foreach ($post['chapter_num'] as $key => $value) {
                    $chapter[] = [
                        'video_id' => $post['id'],
                        'number' => $value,
                        'title' => $post['chapter_title'][$key],
                        'source_url' => $post['chapter_video'][$key],
                    ];
                }
                (new VideoSection())->insertAll($chapter);

            }


            $videoModel->commit();
        } catch (\Exception $e) {
            $videoModel->rollback();
            return json(['code' => 0, 'msg' => $e->getMessage()]);
        }

        return json(['code' => 1, 'msg' => 'success']);
    }

    public function delete(Request $request)
    {
        $id = $request->post('id');
        (new \app\common\model\Video())->where(['id'=>$id])->update(['delete_time'=>time()]);

        return json(['code'=>1,'msg'=>'success']);
    }

    public function uploadPic()
    {
        $path = 'video/';
        return (new Upload())->uploadOnePic($path);
    }

    public function upload()
    {
        $path = 'video/';
        return (new Upload())->uploadOneVideo($path);
    }
}
