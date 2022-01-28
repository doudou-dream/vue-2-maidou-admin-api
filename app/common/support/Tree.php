<?php

declare (strict_types=1);

namespace app\common\support;

/**
 * 树
 * @create 2021年12月30日
 * @author maidou
 */
class Tree
{
    /**
     * 生成树型结构所需要的2维数组
     * @var array
     */
    public $data = [];

    /**
     * 生成树型结构所需修饰符号，可以换成图片
     * @var array
     */
    public $icon       = ['│', '├', '└'];
    public $blankspace = "&nbsp;";

    // 查询
    public $idKey       = "id";
    public $parentIdKey = "parent_id";
    public $spacerKey   = "spacer";
    public $depthKey    = "depth";
    public $haschildKey = "haschild";

    // 返回子级key
    public $buildChildKey = "child";

    /**
     * 创建
     */
    public static function create()
    {
        return new static();
    }

    /**
     * 设置配置
     *
     * @param string $key 键值
     * @param string $value 内容
     *
     * @return $this
     */
    public function withConfig($key, $value)
    {
        if (isset($this->{$key})) {
            $this->{$key} = $value;
        }

        return $this;
    }

    /**
     * 构造函数，初始化类
     *
     * @param array 2维数组，例如：
     * array(
     *      1 => array('id'=>'1','parent_id'=>0,'name'=>'一级分类一'),
     *      2 => array('id'=>'2','parent_id'=>0,'name'=>'一级分类二'),
     *      3 => array('id'=>'3','parent_id'=>1,'name'=>'二级分类一'),
     *      4 => array('id'=>'4','parent_id'=>1,'name'=>'二级分类二'),
     *      5 => array('id'=>'5','parent_id'=>2,'name'=>'二级分类三'),
     *      6 => array('id'=>'6','parent_id'=>3,'name'=>'三级分类一'),
     *      7 => array('id'=>'7','parent_id'=>3,'name'=>'三级分类二')
     * )
     */
    public function withData($data = [])
    {
        $this->data = $data;

        return $this;
    }

    /**
     * 构建数组
     *
     * @param string $id 要查询的ID
     * @param string $itemprefix 前缀
     *
     * @return array
     */
    public function build($id = 0, $itemprefix = '', $depth = 0): array
    {
        $child = $this->getListChild($this->data, $id);
        if (!is_array($child)) {
            return [];
        }

        $data   = [];
        $number = 1;

        $total = count($child);
        foreach ($child as $id => $value) {
            $info = $value;

            $j = $k = '';
            if ($number == $total) {
                if (isset($this->icon[2])) {
                    $j .= $this->icon[2];
                }
                $k = $itemprefix ? $this->blankspace : '';
            } else {
                if (isset($this->icon[1])) {
                    $j .= $this->icon[1];
                }
                $k = $itemprefix ? (isset($this->icon[0]) ? $this->icon[0] : '') : '';
            }
            $spacer                 = $itemprefix ? $itemprefix.$j : '';
            $info[$this->spacerKey] = $spacer;

            // 深度
            $info[$this->depthKey] = $depth;

            $childList = $this->build($value[$this->idKey], $itemprefix.$k.$this->blankspace, $depth + 1);
            if (!empty($childList)) {
                $info[$this->buildChildKey] = $childList;
            }

            $data[] = $info;
            $number++;
        }

        return $data;
    }

    /**
     * 所有父节点
     *
     * @param array $list 数据集
     * @param string|int $parentId 节点的parent_id
     * @param string $sort 排序
     *
     * @return array
     */
    public function getListParents($list = [], $parentId = '', $sort = 'desc'): array
    {
        if (empty($list) || !is_array($list)) {
            return [];
        }

        $result = [];
        foreach ($list as $value) {
            if ((string)$value[$this->idKey] == (string)$parentId) {
                $result[] = $value;

                $parent = $this->getListParents($list, $value[$this->parentIdKey], $sort);
                if (!empty($parent)) {
                    if ($sort == 'asc') {
                        $result = array_merge($result, $parent);
                    } else {
                        $result = array_merge($parent, $result);
                    }
                }
            }
        }

        return $result;
    }

    /**
     * 所有父节点的ID列表
     *
     * @param array $list 数据集
     * @param string|int $parentId 节点的parent_id
     *
     * @return array
     */
    public function getListParentsId($list = [], $parentId = ''): array
    {
        $parents = $this->getListParents($list, $parentId);
        if (empty($parents)) {
            return [];
        }

        $ids = [];
        foreach ($parents as $parent) {
            $ids[] = $parent[$this->idKey];
        }

        return $ids;
    }

    /**
     * 获取当前ID的所有子节点
     *
     * @param array $list 数据集
     * @param string|int $id 当前id
     * @param string $sort 排序
     *
     * @return array
     */
    public function getListChildren(array $list = [], $id = '', $sort = 'desc')
    {
        if (empty($list) || !is_array($list)) {
            return [];
        }

        $result = [];
        foreach ($list as $value) {
            if ((string)$value[$this->parentIdKey] == (string)$id) {
                $result[] = $value;

                $child = $this->getListChildren($list, $value[$this->idKey], $sort);
                if (!empty($child)) {
                    if ($sort == 'asc') {
                        $result = array_merge($result, $child);
                    } else {
                        $result = array_merge($child, $result);
                    }
                }
            }
        }

        return $result;
    }

    /**
     * 获取当前ID的所有子节点id
     *
     * @param array $list 数据集
     * @param string|int $id 当前id
     *
     * @return array
     */
    public function getListChildrenId(array $list = [], $id = ''): array
    {
        $childs = $this->getListChildren($list, $id);
        if (empty($childs)) {
            return [];
        }

        $ids = [];
        foreach ($childs as $child) {
            $ids[] = $child[$this->idKey];
        }

        return $ids;
    }

    /**
     * 得到子级第一级数组
     *
     * @param array $list 数据集
     * @param string|int $id 当前id
     *
     * @return array
     */
    public function getListChild(array $list, $id): array
    {
        if (empty($list) || !is_array($list)) {
            return [];
        }

        $id      = (string)$id;
        $newData = [];
        foreach ($list as $key => $data) {
            $dataParentId = (string)$data[$this->parentIdKey];
            if ($dataParentId == $id) {
                $newData[$key] = $data;
            }
        }

        return $newData;
    }

    /**
     * 获取ID自己的数据
     *
     * @param array $list 数据集
     * @param string|int $id 当前id
     *
     * @return array
     */
    public function getListSelf(array $list, $id): array
    {
        if (empty($list) || !is_array($list)) {
            return [];
        }

        $id = (string)$id;
        foreach ($list as $key => $data) {
            $dataId = (string)$data[$this->idKey];
            if ($dataId == $id) {
                return $data;
            }
        }

        return [];
    }

    /**
     * 将 build 的结果返回为二维数组
     *
     * @param array $data 数据
     * @param int $parentId 父级ID
     *
     * @return array
     */
    public function buildFormatList(array $data = [], int $parentId = 0): array
    {
        if (empty($data)) {
            return [];
        }

        $list = [];
        foreach ($data as $k => $v) {
            if (!empty($v)) {
                if (!isset($v[$this->spacerKey])) {
                    $v[$this->spacerKey] = '';
                }

                $child                 = isset($v[$this->buildChildKey]) ? $v[$this->buildChildKey] : [];
                $v[$this->haschildKey] = $child ? 1 : 0;
                unset($v[$this->buildChildKey]);

                if (!isset($v[$this->parentIdKey])) {
                    $v[$this->parentIdKey] = $parentId;
                }

                $list[] = $v;

                if (!empty($child)) {
                    $list = array_merge($list, $this->buildFormatList($child, $v[$this->idKey]));
                }
            }
        }

        return $list;
    }

}
