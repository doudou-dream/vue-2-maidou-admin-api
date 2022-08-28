<?php

declare (strict_types=1);

namespace app\common\support;

// use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\DocParser;
use app\common\support\annotation;

/**
 * 节点处理类
 * Class Power
 * @package EasyAdmin\auth
 */
class PowerNote
{

    /**
     * @var string 当前文件夹
     */
    protected $basePath;

    /**
     * @var string 命名空间前缀
     */
    protected $baseNamespace;

    /**
     * 构造方法
     * Power constructor.
     *
     * @param string $basePath 读取的文件夹
     * @param string $baseNamespace 读取的命名空间前缀
     */
    public function __construct(string $basePath, string $baseNamespace)
    {
        $this->basePath      = $basePath;
        $this->baseNamespace = $baseNamespace;

        return $this;
    }

    /**
     * 获取所有权限
     * @return array
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ReflectionException
     */
    public function getList()
    {
        [$nodeList, $controllerList] = [[], $this->getControllerList()];

        if (!empty($controllerList)) {
            // AnnotationRegistry::registerLoader('class_exists');
            $parser = new DocParser();
            $parser->setIgnoreNotImportedAnnotations(true);
            $reader = new AnnotationReader($parser);
            foreach ($controllerList as $controllerFormat => $controller) {
                // 获取类和方法的注释信息
                $reflectionClass = new \ReflectionClass($controller);
                $methods         = $reflectionClass->getMethods();
                $actionList      = [];
                // 遍历读取所有方法的注释的参数信息
                foreach ($methods as $key => $method) {
                    // 读取方法注释注解
                    $desc = $reader->getMethodAnnotation($method, annotation\Desc::class);
                    // 读取方法的注解
                    $nodeAnnotation = $reader->getMethodAnnotation($method, annotation\Power::class);
                    if (!empty($nodeAnnotation)) {
                        // 权限
                        $actionList[] = [
                            'title'       => $nodeAnnotation->title ?? '',
                            'url'         => $nodeAnnotation->url ?? '',
                            'method'      => $nodeAnnotation->method ?? '',
                            'slug'        => $nodeAnnotation->slug ?? ($controllerFormat.'/'.$method->name),
                            'description' => $desc->value ?? ($nodeAnnotation->desc ?? ''),
                            'listorder'   => $nodeAnnotation->listorder ?? '',
                        ];
                    }
                }
                // 读取类的注解
                $controllerAnnotation = $reader->getClassAnnotation($reflectionClass, annotation\Power::class);
                // 读取类注释注解
                $controllerDesc       = $reader->getClassAnnotation($reflectionClass, annotation\Desc::class);
                if (!empty($actionList) && !empty($controllerAnnotation)) {
                    // 权限
                    $nodeList[] = [
                        'title'       => $controllerAnnotation->title ?? '',
                        'url'         => $controllerAnnotation->url ?? '',
                        'method'      => $controllerAnnotation->method ?? '',
                        'slug'        => $controllerAnnotation->slug ?? $controllerFormat,
                        'description' => $controllerDesc->value ?? ($controllerAnnotation->desc ?? ''),
                        'listorder'   => $controllerAnnotation->listorder ?? '',
                        'child'       => $actionList,
                    ];
                }
            }
        }

        return $nodeList;
    }

    /**
     * 获取所有控制器
     * @return array
     */
    public function getControllerList()
    {
        return $this->readControllerFiles($this->basePath);
    }

    /**
     * 遍历读取控制器文件
     *
     * @param $path
     *
     * @return array
     */
    protected function readControllerFiles($path)
    {
        [$list, $temp_list, $dirExplode] = [[], scandir($path), explode($this->basePath, $path)];
        $middleDir = isset($dirExplode[1]) && !empty($dirExplode[1]) ? str_replace('/', '\\',
                substr($dirExplode[1], 1))."\\" : null;

        foreach ($temp_list as $file) {
            // 排除根目录和没有开启注解的模块
            if ($file == ".." || $file == ".") {
                continue;
            }
            if (is_dir($path.DIRECTORY_SEPARATOR.$file)) {
                // 子文件夹，进行递归
                $childFiles = $this->readControllerFiles($path.DIRECTORY_SEPARATOR.$file);
                $list       = array_merge($childFiles, $list);
            } else {
                // 判断是不是控制器
                $fileExplodeArray = explode('.', $file);
                if (count($fileExplodeArray) != 2 || end($fileExplodeArray) != 'php') {
                    continue;
                }
                // 根目录下的文件
                $className               = str_replace('.php', '', $file);
                $controllerFormat        = str_replace('\\', '.', $middleDir).$this->humpToLine(lcfirst($className));
                $list[$controllerFormat] = "{$this->baseNamespace}\\{$middleDir}".$className;
            }
        }

        return $list;
    }


    /**
     * 驼峰转下划线
     *
     * @param $str
     *
     * @return null|string|string[]
     */
    public static function humpToLine($str)
    {
        $str = preg_replace_callback('/([A-Z]{1})/', function ($matches) {
            return '_'.strtolower($matches[0]);
        }, $str);

        return $str;
    }

}
