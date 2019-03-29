<?php

namespace app\admin\controller;

use Exception;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

use think\Controller;

class Excel extends Controller
{

    /**
     * 导出Excel
     *
     * @param $xlsTitle
     * @param $tableData
     * @param $cellName
     * @param $filePath
     * @param $fileName
     * @param bool $isDownload
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function exportExcel($xlsTitle, $tableData, $cellName, $filePath, $fileName,  $isDownload = true)
    {
        set_time_limit(0);
        $spreadsheet = new Spreadsheet();
        $spreadsheet->setActiveSheetIndex(0);
        $spreadsheet->getActiveSheet()->setTitle($xlsTitle);

        $cellArray1 = $cellName;

        if($tableData){
            //$limit = ceil(count($tableData) / 1000);
            $index1 = 2;
            foreach ($tableData as $k => $item) {
                $spreadsheet->getActiveSheet()->getRowDimension($index1)->setRowHeight(14.25);
                foreach ($cellArray1 as $key1 => $cell1) {
                    if($k <= 0){
                        $spreadsheet->getActiveSheet()->setCellValue($key1 . '1', $cell1[1]);
                        //Set Font
                        $spreadsheet->getDefaultStyle()->getFont()->setSize(10);
                        $spreadsheet->getDefaultStyle()->getFont()->setName("Arial");
                        $spreadsheet->getActiveSheet()->getStyle($key1 . '1')->getFont()->setBold(true);
                    }
                    //$spreadsheet->getActiveSheet()->getColumnDimension($key1)->setAutoSize(true);
                    //$spreadsheet->getActiveSheet()->getColumnDimension($key1)->setWidth(20);
                    //$spreadsheet->getActiveSheet()->getStyle($key1 . ($index1))->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
                    if(is_object($tableData)){
                        try {
                            $spreadsheet->getActiveSheet()->setCellValueExplicit($key1 . ($index1), $item->{$cell1[0]}(),DataType::TYPE_STRING);
                            //$spreadsheet->getActiveSheet()->setCellValue($key1 . ($index1), $item->{$cell1[0]}()); // Custom Attribute function
                        } catch (Exception $e) {
                            $spreadsheet->getActiveSheet()->setCellValueExplicit($key1 . ($index1), $item->{$cell1[0]},DataType::TYPE_STRING);
                            //$spreadsheet->getActiveSheet()->setCellValue($key1 . ($index1), $item->{$cell1[0]});
                        }
                    }else{
                        // 解决长数字串显示为科学计数（1.23E+12）
                        $spreadsheet->getActiveSheet()->setCellValueExplicit($key1 . ($index1), $item[$cell1[0]],DataType::TYPE_STRING);
                        //$spreadsheet->getActiveSheet()->setCellValue($key1 . ($index1), $item[$cell1[0]]);
                    }
                }
                // 每执行1000条数据
                if($index1 % 1000 == 0){
                    sleep(1);
                }
                $index1 ++;
            }

            //检查文件或目录是否存在
            if(!file_exists($filePath)){
                mkdir($filePath, 0777, true);
            }
            $writer = new Xlsx($spreadsheet);
            $writer->save($fileName . '.xls'); // or .xlsx

            if($isDownload){
                $xlsTitle = iconv('utf-8', 'gb2312', $xlsTitle); // 文件名称
                $downFileName = basename($fileName,".".pathinfo($fileName, PATHINFO_EXTENSION));
                header('pragma:public');
                header('Content-type:application/vnd.ms-excel;charset=utf-8;name="'.$xlsTitle.'.xls"');
                header("Content-Disposition:attachment;filename=$downFileName.xls"); // attachment 新窗口打印,inline 本窗口打印

                $writer->save('php://output');
                //exit;
            }
            //sleep(1);
        }
        exit;
    }

}