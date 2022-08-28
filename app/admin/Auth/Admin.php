<?php

declare (strict_types=1);

namespace app\admin\auth;

use tauthz\facade\Enforcer;

class Admin
{
    /*
     * id
     */
    protected $id = null;

    /*
     * data
     */
    protected $data = [];

    /*
     * 设置 id
     */
    public function withId($id): Admin
    {
        $this->id = $id;

        return $this;
    }

    /*
     * 获取 id
     */
    public function getId()
    {
        return $this->id;
    }

    /*
     * 设置 data
     */
    public function withData($data): Admin
    {
        $this->data = $data;

        return $this;
    }

    /*
     * 获取 data
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * 是否为顶级账号
     */
    public function isRoot()
    {
        return ((int) $this->data['is_root'] === 1);
    }

    /**
     * 判断是否有权限
     */
    public function hasAccess($slug, $method = 'GET')
    {
        if (!Enforcer::enforce((string) $this->id, $slug, $method)) {
            return false;
        }

        return true;
    }

    /**
     * 判断是否有角色
     */
    public function isGroup()
    {
        if (empty($this->data['groups']) && !$this->isRoot()) {
            return false;
        }

        return true;
    }
}
