<?php


namespace app\admin\extension;


use app\admin\model\AuthRule as AuthRuleModel;
use app\common\support\PowerNote;
use Doctrine\Common\Annotations\AnnotationException;
use think\facade\Log;
use think\helper\Arr;

class Rule
{
    /**
     * 写入权限
     *
     * @param array $data
     * @param int $parentId
     *
     * @return \app\admin\model\AuthRule|false|\think\Model
     */
    public static function create($data = [], $parentId = 0)
    {
        if (empty($data)) {
            return false;
        }
        $rule = AuthRuleModel::where('slug', Arr::get($data, 'slug'))->find();
        if (empty($rule)) {
            $lastOrder = AuthRuleModel::max('listorder');
            $rule      = AuthRuleModel::create([
                'parent_id'   => $parentId,
                'listorder'   => $lastOrder + 1,
                'title'       => Arr::get($data, 'title'),
                'url'         => Arr::get($data, 'url'),
                'method'      => Arr::get($data, 'method'),
                'slug'        => Arr::get($data, 'slug'),
                'description' => Arr::get($data, 'description', ''),
            ]);
        }


        $children = Arr::get($data, 'child', []);
        foreach ($children as $child) {
            static::create($child, $rule->id);
        }

        return $rule;
    }

    /**
     * 权限初始化
     *
     * @param string $file 模块目录地址[默认：admin/controller]
     *
     * @return bool
     */
    public static function init($file = 'admin'.DIRECTORY_SEPARATOR.'controller')
    {
        $basePath      = base_path().$file;
        $baseNamespace = 'app'.DIRECTORY_SEPARATOR.$file;
        try {
            $nodeList = (new PowerNote($basePath, $baseNamespace))->getList();
        } catch (AnnotationException $e) {
            Log::error($e);

            return false;
        } catch (\ReflectionException $e) {
            Log::error($e);

            return false;
        }
        foreach (($nodeList ?? []) as $item) {
            static::create($item);
        }

        return true;
    }

}
