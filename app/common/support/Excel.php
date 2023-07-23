<?php

namespace app\common\support;

use app\common\traits\ResponseJson;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use think\facade\Request;

class Excel
{
    use ResponseJson;

    function save($data, $headers, $filename): \think\response\Json
    {
        // 创建新的 Excel 对象
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // 设置标题单元格格式
        $sheet->getStyle('A1:Z1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1:Z1')->getAlignment()->setHorizontal('center')->setVertical('center');

        // 设置标题行内容
        // 26个字母数组
        $titleC = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R'];
        $ti = 0;
        foreach ($headers as $header) {
            $sheet->setCellValue($titleC[$ti] . '1', $header);
            // 设置单元格宽度
            $sheet->getColumnDimension($titleC[$ti])->setWidth(4 * mb_strlen($header, 'UTF-8'));
            ++$ti;
        }
        // 导出数据
        $row = 2; // 行号

        foreach ($data as $rowData) {
            $column = 'A'; // 列号

            foreach ($rowData as $cellData) {
                if ($cellData['type'] === 'string') {
                    $sheet->setCellValue($column . $row, $cellData['value']);
                    $sheet->getStyle($column . $row)->getAlignment()->setHorizontal('center')->setVertical('center');
                } elseif ($cellData['type'] === 'image') {
                    $drawing = new Drawing();
                    $drawing->setName('Image');
                    $drawing->setDescription('Image');
                    $drawing->setPath($cellData['value']);
                    $drawing->setCoordinates($column . $row);

                    // 设置图片的宽度和高度
                    $drawing->setWidthAndHeight(50, 50);
                    // 设置图片在单元格内的偏移量
                    $drawing->setOffsetX(5);
                    $drawing->setOffsetY(5);

                    $drawing->setWorksheet($sheet);

                    // 调整单元格大小以适应图片
                    $sheet->getRowDimension($row)->setRowHeight(50);
                    // 设置图片在单元格内的对齐方式
                    $sheet->getStyle($column . $row)->getAlignment()->setHorizontal('center')->setVertical('center');
                }

                // 更新列号
                ++$column;
            }
            // 更新行号
            ++$row;
        }
        $folderPath = './excel/' . date('Y-m-d').'/';
        if (!file_exists($folderPath) || !is_dir($folderPath)) {
            mkdir($folderPath, 0777, true); // 创建文件夹，并设置权限为777（可根据实际需求调整）
        }
        $filePath =  $folderPath. MD5(time() . random_int(100, 999)) . '-' . $filename . '.xls';
        // 保存 Excel 文件
        $writer = new Xlsx($spreadsheet);
        try {
            $writer->save($filePath);
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
        $domain = Request::domain(true);
        return $this->success('成功', ['url' => $domain . $filePath]);
    }
}
