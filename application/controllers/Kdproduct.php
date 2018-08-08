<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/8
 * Time: 15:39
 */

class Kdproduct extends MY_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->library('PHPExcel');
	}
	
	
	public function index()
	{
		
		extract($_POST);
		
		$wh = [];
		
		if (!in_array($_SESSION['role_id'], [1])) {
			$wh['kd_man'] = $_SESSION['id'];
		}
		
		if ($id) $wh['kp.id'] = $id;
//		if ($sku) $wh['kp.zt_sku'] = $sku;
		if ($cat_id) $wh['kp.cat_id'] = $cat_id;
		if ($title) $wh['like'] = ['kp.title' => $title];
		if ($kd_man) $wh['kp.kd_man'] = $kd_man;
		if ($status) $wh['kp.status'] = $status;
		
		if ($sku) {
			$sku = $this->db->escape($sku);
			$wh["(zt_sku = $sku or mt_sku = $sku)"] = null;
		}
		
		if (is_array($dates) && $dates[0]) {
			$wh['kp.addtime >='] = strtotime($dates[0]);
			$wh['kp.addtime <'] = strtotime($dates[1]) + 86400;
		}
		
		$currentpage = element('currentPage', $_POST, 1);
		$pagesize = element('pageSize', $_POST, 10);
		$first_row = ($currentpage - 1) * $pagesize;
		
		$fields = "kp.*,u.realname,cp.imgs,cp.cd_url,c.name_cn,c.code,c.cat_1,c.cat_2,c.cat_3,c.cat_4,s.store_code";
		
		$joins[] = ['t_user u', 'u.id=kp.kd_man', 'left'];
		$joins[] = ['cd_product cp', 'cp.id=kp.product_id', 'left'];
		$joins[] = ['t_category c', 'c.id=kp.cat_id', 'left'];
		$joins[] = ['t_store s', 's.id=kp.store', 'left'];
		
		
		$ret1 = $this->getJoinData('kd_product kp', "count(*) as num", $joins, $wh, 0);
		$rows = $this->getJoinData('kd_product kp', $fields, $joins, $wh, "kp.id desc", $first_row, $pagesize);
		
		$count = $ret1[0]['num'];
		
		$query = str_replace("\n", "\t", $this->db->last_query());
		
		$kdstatus = $this->config->item("kdstatus");
		$sexes = $this->config->item("sexes");
		
		$cates = $this->categorys();
		$users = $this->users();
		$stores = $this->stores();
		$brands = $this->brands();
		
		$data['sexes'] = $sexes;
		$data['brands'] = $brands;
		$data['cates'] = $cates;
		$data['users'] = $users;
		$data['stores'] = $stores;
		$data['kdstatus'] = $kdstatus;
		$data['data'] = $rows;
		$data['total'] = intval($count);
		$data['query'] = $query;
		
		echo json_encode($data);
		
	}
	
	
	public function edit()
	{
		if (!$_POST) return;
		extract($_POST);
		if (!in_array($_SESSION['role_id'], [1])) {        // 只能修改自己刊登的产品
			$where['kd_man'] = $_SESSION['id'];
		}
		
		$where['id'] = intval($id);
		$data = array(
			'title' => $title,
			'title1' => $title1,
			'price' => $price,
			'shipfee' => $shipfee,
			'stock' => $stock,
			'store' => $store,
			'brand' => $brand,
			'audiences' => $audiences,
			'sex' => $sex,
			'maidian' => $maidian,
			'desc' => $desc,
			'note' => $note,
		);
		
		$this->db->where($where)->update("kd_product", $data);
		re_json([], 0, '更新成功');
	}
	
	public function delete()
	{
		$id = intval($this->input->get('id'));
		$where['id'] = $id;
		
		if (!in_array($_SESSION['role_id'], [1])) {
			$where['kd_man'] = $_SESSION['id'];
		}
		
		$row = $this->db->limit(1)->where($where)->get('kd_product')->row_array();
		$row2 = $this->db->limit(1)->where('product_id', $row['product_id'])->select('count(*) as num')->get('kd_product')->row_array();
		
		if ($row2['num'] < 2) {
			
			$row3 = $this->db->limit(1)->where("id", $row['product_id'])->select('kder_id')->get('cd_product')->row_array();
			$kder_ids = explode(',', $row3['kder_id']);
			
			if (($key = array_search($_SESSION['id'], $kder_ids)) !== false) {
				unset($kder_ids[$key]);
			}

			$data['kder_id'] = implode(',', $kder_ids);
			$this->db->where("id", $row['product_id'])->update("cd_product", $data);
			ignore_user_abort(true);
		}
		
		$this->db->limit(1)->where($where)->delete("kd_product");
		
		if ($this->db->affected_rows()) {
			re_json([], 0, '删除成功');
		} else {
			re_json([], 1, '删除失败');
		}
	}
	
	// 根据搜索条件，导出产品
	public function export_products()
	{
		set_time_limit(600);
		extract($_GET);
		$wh = [];
		
		if (!in_array($_SESSION['role_id'], [1])) {
			$wh['kd_man'] = $_SESSION['id'];
		}
		
		if ($id) $wh['kp.id'] = $id;
		if ($sku) $wh['kp.zt_sku'] = $sku;
		if ($cat_id) $wh['kp.cat_id'] = $cat_id;
		if ($title) $wh['like'] = ['kp.title' => $title];
		if ($kd_man) $wh['kp.kd_man'] = $kd_man;
		if ($status) $wh['kp.status'] = $status;
		if ($ids) $wh['in'] = ["kp.id", explode(',', $ids)];
		
		if ($dates) {
			$dates = json_decode($dates);
			$wh['kp.addtime >='] = strtotime($dates[0]);
			$wh['kp.addtime <'] = strtotime($dates[1]) + 86400;
		}
		
		$fields = "kp.*,cp.cd_id,cp.imgs imgs1,c.code";
		
		$joins[] = ['cd_product cp', 'cp.id=kp.product_id', 'left'];
		$joins[] = ['t_category c', 'c.id=kp.cat_id', 'left'];
		
		$ret1 = $this->getJoinData('kd_product kp', "count(*) as num", $joins, $wh, 0);
		$count = $ret1[0]['num'];
		
		$brands = $this->brands();
		
		$sexes = $this->config->item("sexes_fr");
		$audiences = $this->config->item("audiences_fr");
		
		$objPHPExcel = new PHPExcel();
		$sheet = $objPHPExcel->getActiveSheet(0);
		
		$this->xlsx_header($sheet);
		
		$pagesize = 300;
		$times = ceil($count / $pagesize);
		$j = 4;
		
		for ($i = 0; $i < $times; $i++) {
			$first_row = $i * $pagesize;
			$rows = $this->getJoinData('kd_product kp', $fields, $joins, $wh, 0, $first_row, $pagesize);
			
			foreach ($rows as $k => $row) {
				
				$imgs = explode("\n", $row['imgs1']);
				
				$variant = '';
				if ($row['size'] || $row['color']) {
					$variant = 'variant';
				}
				
				$sheet->setCellValue('A' . $j, $row['zt_sku']);
				$sheet->setCellValue('B' . $j, $row['ean']);
				$sheet->setCellValue('C' . $j, $brands[$row['brand']]);
				$sheet->setCellValue('D' . $j, $variant);
				$sheet->setCellValue('E' . $j, $row['code']);
				$sheet->setCellValue('F' . $j, $row['title1']);
				$sheet->setCellValue('G' . $j, $row['title']);
				$sheet->setCellValue('H' . $j, $row['desc']);
				$sheet->setCellValue('I' . $j, $imgs[0]);
				$sheet->setCellValue('J' . $j, $row['mt_sku']);
				$sheet->setCellValue('K' . $j, $row['size']);
				$sheet->setCellValue('L' . $j, $row['color']);
				$sheet->setCellValue('M' . $j, $row['maidian']);
				$sheet->setCellValue('N' . $j, $imgs[1]);
				$sheet->setCellValue('O' . $j, $imgs[2]);
				$sheet->setCellValue('P' . $j, $imgs[3]);
				$sheet->setCellValue('Q' . $j, '');
				$sheet->setCellValue('R' . $j, '');
				$sheet->setCellValue('S' . $j, '');
				$sheet->setCellValue('T' . $j, '');
				$sheet->setCellValue('U' . $j, '');
				$sheet->setCellValue('V' . $j, '');
				$sheet->setCellValue('W' . $j, '');
				$sheet->setCellValue('X' . $j, '');
				$sheet->setCellValue('Y' . $j, '');
				$sheet->setCellValue('Z' . $j, '');
				$sheet->setCellValue('AA' . $j, $row['color']);
				$sheet->setCellValue('AB' . $j, $sexes[$row['sex']]);
				$sheet->setCellValue('AC' . $j, '');
				$sheet->setCellValue('AD' . $j, '');
				$sheet->setCellValue('AE' . $j, $audiences[$row['audiences']]);
				$j++;
			}
			
		}
		
		$file = 'CD产品表_' . date('Ymd-Hi') . '.xls';
//		$objPHPExcel->setActiveSheetIndex(0);
		
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header("Content-Disposition: attachment;filename=\"$file\"");
		header('Cache-Control: max-age=0');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
		header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
		header('Pragma: public'); // HTTP/1.0
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$objWriter->save('php://output');
		
	}
	
	private function xlsx_header($sheet)
	{

		$style = array(
			'font' => array(
				'font' => 18,
				'bold' => true,
				'color' => array('rgb' => 'ffffff'),
			),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
				'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
			),
		);
		$style2 = array(
			'borders' => array(
				'outline' => array(
					'style' => PHPExcel_Style_Border::BORDER_THIN,
					'color' => array('argb' => '111111'),
				),
			),
		);
		
		for ($column = 'A'; $column <= "Z"; $column++) {
			if ($column == 'AF') break;
			if (in_array($column, ['B', 'H', 'M'])) continue;
			$sheet->getColumnDimension($column)->setAutoSize(true);
		}
		
		$sheet->getColumnDimension('H')->setWidth(100);
		$sheet->getColumnDimension('B')->setWidth(20);
		$sheet->getColumnDimension('M')->setWidth(50);
		$sheet->getRowDimension('1')->setRowHeight(50);
		$sheet->getRowDimension('2')->setRowHeight(30);
		$sheet->getRowDimension("3")->setRowHeight(50);
		$sheet->getStyle('A1:B1')->getFont()->setSize(12);
		
		$sheet->setCellValue('A1', 'Model :');
		$sheet->setCellValue('B1', 'SOUMISSION CREATION PRODUITS_MK');
		$sheet->getStyle('B1:B3')->getAlignment()->setWrapText(true);
		
		// 合并单元格
		$sheet->mergeCells('A2:I2');
		$sheet->setCellValue('A2', "Required Data");
		
		$sheet->mergeCells('J2:L2');
		$sheet->setCellValue('J2', "Required Data if variant sizes");
		
		$sheet->mergeCells('M2:W2');
		$sheet->setCellValue('M2', "Optional data");
		
		$sheet->mergeCells('X2:AE2');
		$sheet->setCellValue('X2', "Données spécifiques");
		
		
		//设置填充背景色
		$sheet->getStyle('A2:AE3')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
		$sheet->getStyle('A2:I3')->getFill()->getStartColor()->setARGB('16365C');
		$sheet->getStyle('J2:L3')->getFill()->getStartColor()->setARGB('C00000');
		$sheet->getStyle('M2:W3')->getFill()->getStartColor()->setARGB('366092');
		$sheet->getStyle('X2:AE3')->getFill()->getStartColor()->setARGB('76933C');
		
		$sheet->getStyle('A2:AE3')->applyFromArray($style);
		
		$headers = [
			'Your reference',
			'EAN (optional for fashion and home furniture)',
			'Brand',
			'Nature of product',
			'Category code',
			'Basket short wording',
			'Long title',
			'Product description',
			'Picture 1 (jpeg)',
			'Parent SKU',
			'Size (bounded)',
			'Marketing color',
			'Marketing description',
			'Picture 2 (jpeg)',
			'Picture 3 (jpeg)',
			'Picture 4 (jpeg)',
			'Browsing / classification / shelf',
			'ISBN',
			'MFPN',
			'Length (cm)',
			'Width (cm)',
			'Height (cm)',
			'Weight (kg)',
			'Avertissement(s)',
			'Commentaire',
			'Couleur(s)',
			'Couleur principale',
			'Genre',
			'Licence',
			'Sports',
			'Type de public',
		];
		
		$i = 0;
		for ($column = 'A'; $column <= "Z"; $column++) {
			if ($column == 'AF') break;
			$sheet->setCellValue($column . "3", $headers[$i++]);
			$sheet->getStyle($column . "3")->applyFromArray($style2);        // 单元格加边框
		}
		
	}
	
	
	// 根据搜索条件，导出价格
	public function export_prices()
	{
		set_time_limit(600);
		extract($_GET);
		$wh = [];
		
		if (!in_array($_SESSION['role_id'], [1])) {
			$wh['kd_man'] = $_SESSION['id'];
		}
		
		if ($id) $wh['kp.id'] = $id;
		if ($sku) $wh['kp.zt_sku'] = $sku;
		if ($cat_id) $wh['kp.cat_id'] = $cat_id;
		if ($title) $wh['like'] = ['kp.title' => $title];
		if ($kd_man) $wh['kp.kd_man'] = $kd_man;
		if ($status) $wh['kp.status'] = $status;
		if ($ids) $wh['in'] = ["kp.id", explode(',', $ids)];
		
		if ($dates) {
			$dates = json_decode($dates);
			$wh['kp.addtime >='] = strtotime($dates[0]);
			$wh['kp.addtime <'] = strtotime($dates[1]) + 86400;
		}

		$fields = "kp.*";
		
		$ret1 = $this->getJoinData('kd_product kp', "count(*) as num", $joins, $wh, 0);
		$count = $ret1[0]['num'];
	
		$objPHPExcel = new PHPExcel();
		$sheet = $objPHPExcel->getActiveSheet(0);
		$this->xlsx_header2($sheet);
		
		$pagesize = 300;
		$times = ceil($count / $pagesize);
		$j = 6;
		
		for ($i = 0; $i < $times; $i++) {
			$first_row = $i * $pagesize;
			$rows = $this->getJoinData('kd_product kp', $fields, $joins, $wh, 0, $first_row, $pagesize);
			
			foreach ($rows as $k => $row) {
				
				$sheet->setCellValue('A' . $j, $row['zt_sku']);
				$sheet->setCellValue('B' . $j, $row['ean']);
				$sheet->setCellValue('C' . $j, 'New - New');
				$sheet->setCellValue('D' . $j, $row['stock']);
				$sheet->setCellValue('E' . $j, $row['price']);
				$sheet->setCellValue('F' . $j, '20.00');
				$sheet->setCellValue('G' . $j, '0.00');
				$sheet->setCellValue('H' . $j, '0.00');
				$sheet->setCellValue('I' . $j, '');
				$sheet->setCellValue('J' . $j, '');
				$sheet->setCellValue('K' . $j, '');
				$sheet->setCellValue('L' . $j, '');
				$sheet->setCellValue('M' . $j, '');
				$sheet->setCellValue('N' . $j, '');
				$sheet->setCellValue('O' . $j, '');
				$sheet->setCellValue('P' . $j, '');
				$sheet->setCellValue('Q' . $j, '');
				$sheet->setCellValue('R' . $j, '');
				$sheet->setCellValue('S' . $j, '');
				$sheet->setCellValue('T' . $j, '');
				$sheet->setCellValue('U' . $j, '');
				$sheet->setCellValue('V' . $j, '4');
				$sheet->setCellValue('W' . $j, '0');
				$sheet->setCellValue('X' . $j, '0');
				$sheet->setCellValue('Y' . $j, '0');
				$sheet->setCellValue('Z' . $j, '0');
				$sheet->setCellValue('AA' . $j, '0');
				$sheet->setCellValue('AB' . $j, '0');
				$j++;
			}
			
		}
		
		$file = 'CD价格表_' . date('Ymd-Hi') . '.xls';
		
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header("Content-Disposition: attachment;filename=\"$file\"");
		header('Cache-Control: max-age=0');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
		header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
		header('Pragma: public'); // HTTP/1.0
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$objWriter->save('php://output');
		
	}
	
	
	private function xlsx_header2($sheet)
	{
		
		$style1 = array(
			'font' => array(
				'font' => 20,
				'bold' => true,
				'color' => array('rgb' => '000000'),
			),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
				'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
			),
		);
		$style2 = array(
			'font' => array(
				'font' => 12,
				'color' => array('rgb' => '000000'),
			),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
				'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
			),
		);
		
		
		for ($column = 'A'; $column <= "Z"; $column++) {
			if ($column == 'AC') break;
			$sheet->getColumnDimension($column)->setWidth(20);
		}
		
		$sheet->getRowDimension('1')->setRowHeight(40);
		$sheet->getRowDimension('5')->setRowHeight(30);
		
		$sheet->getColumnDimension("L")->setWidth(40);
		$sheet->getColumnDimension("V")->setWidth(30);
		
		$sheet->getStyle('4')->getAlignment()->setWrapText(true);
		$sheet->getStyle('5')->getAlignment()->setWrapText(true);

// row 1
		$sheet->mergeCells('A1:L1');
		$sheet->setCellValue('A1', "PRODUCT");
		
		$sheet->mergeCells('M1:S1');
		$sheet->setCellValue('M1', "PROMOTION TYPE");
		
		$sheet->mergeCells('T1:U1');
		$sheet->setCellValue('T1', "PRICE MATCHING");
		
		$sheet->setCellValue('V1', "PREPARATION");
		
		$sheet->mergeCells('W1:AB1');
		$sheet->setCellValue('W1', "DELIVERY FEES (€)(incl.tax)");

// row 2 3
		$sheet->mergeCells('A2:L2');
		$sheet->setCellValue('A2', "Basic datas to publish your offers");
		
		$sheet->mergeCells('M2:S3');
		$sheet->setCellValue('M2', "Required only if you choose one of this option");
		
		$sheet->mergeCells('T2:U3');
		$sheet->setCellValue('T2', "Required only if you choose this option");
		
		$sheet->mergeCells('V2:V3');
		$sheet->setCellValue('V2', "Required only if you choose one of this option");
		
		$sheet->mergeCells('W2:AB2');
		$sheet->setCellValue('W2', "At home (Small parcel OR Big parcel to complete for all the offers)");

// ROW 3
		$sheet->setCellValue('A3', "Required");
		$sheet->setCellValue('B3', "Optional");
		
		$sheet->mergeCells('C3:H3');
		$sheet->setCellValue('C3', "Required");
		
		$sheet->mergeCells('I3:L3');
		$sheet->setCellValue('I3', "Optional");
		
		$sheet->mergeCells('W3:AB3');
		$sheet->setCellValue('W3', "Small parcel (Less than 30 kg)");

// row 4
		$sheet->mergeCells('N4:O4');
		$sheet->setCellValue('N4', "Start");
		
		$sheet->mergeCells('P4:Q4');
		$sheet->setCellValue('P4', "End");
		
		$sheet->mergeCells('W4:X4');
		$sheet->setCellValue('W4', "Standard");
		
		$sheet->mergeCells('Y4:Z4');
		$sheet->setCellValue('Y4', "Tracked");
		
		$sheet->mergeCells('AA4:AB4');
		$sheet->setCellValue('AA4', "Registered");

// row 4 5
		$sheet->mergeCells('A4:A5');
		$sheet->setCellValue('A4', "Your reference");
		
		$sheet->mergeCells('B4:B5');
		$sheet->setCellValue('B4', "EAN");
		
		$sheet->mergeCells('C4:C5');
		$sheet->setCellValue('C4', "Product condition");
		
		$sheet->mergeCells('D4:D5');
		$sheet->setCellValue('D4', "Stock");
		
		$sheet->mergeCells('E4:E5');
		$sheet->setCellValue('E4', "Price (€) (incl.tax)");
		
		$sheet->mergeCells('F4:F5');
		$sheet->setCellValue('F4', "VAT (%)");
		
		$sheet->mergeCells('G4:G5');
		$sheet->setCellValue('G4', "Eco Part(€)");
		
		$sheet->mergeCells('H4:H5');
		$sheet->setCellValue('H4', "DEA(€)");
		
		$sheet->mergeCells('I4:I5');
		$sheet->setCellValue('I4', "Packaging unit");
		
		$sheet->mergeCells('J4:J5');
		$sheet->setCellValue('J4', "Product packaging");
		
		$sheet->mergeCells('K4:K5');
		$sheet->setCellValue('K4', "Reference price (€) (incl.tax)");
		
		$sheet->mergeCells('L4:L5');
		$sheet->setCellValue('L4', "Offers comment");
		
		$sheet->mergeCells('M4:M5');
		$sheet->setCellValue('M4', "Promotion type");
		
		$sheet->mergeCells('R4:R5');
		$sheet->setCellValue('R4', "Discount (%)");
		
		$sheet->mergeCells('S4:S5');
		$sheet->setCellValue('S4', "Reference Price (€)(incl.tax) –  Applicable only for Sales");
		
		$sheet->mergeCells('T4:T5');
		$sheet->setCellValue('T4', "Automatically set to best offer");
		
		$sheet->mergeCells('U4:U5');
		$sheet->setCellValue('U4', "Lowest allowable price (€)(incl.tax)");
		
		$sheet->mergeCells('V4:V5');
		$sheet->setCellValue('V4', "Preparation Time(Max working days)");

// ROW 5
		$sheet->setCellValue('N5', "Date (dd/mm/yyyy)");
		$sheet->setCellValue('O5', "Hour (hh:mm)");
		$sheet->setCellValue('P5', "Date (dd/mm/yyyy)");
		$sheet->setCellValue('Q5', "Hour (hh:mm)");
		$sheet->setCellValue('W5', "Main (€)");
		$sheet->setCellValue('X5', "Additionnal (€) Optional");
		$sheet->setCellValue('Y5', "Main (€)");
		$sheet->setCellValue('Z5', "Additionnal (€) Optional");
		$sheet->setCellValue('AA5', "Main (€)");
		$sheet->setCellValue('AB5', "Additionnal (€) Optional");

// 设置样式
		$sheet->getStyle('A1:W1')->applyFromArray($style1);
		$sheet->getStyle('A2:AB5')->applyFromArray($style2);
		
	}
	
	public function import()
	{
		
		$path = $_FILES['file'];
		$filePath = FCPATH . "uploads/kd_report/" . rand2() . '.xlsx';
		
		if (!is_uploaded_file($path["tmp_name"])) {
			re_json([], 1, '非法文件');
			return;
		}
		
		move_uploaded_file($path["tmp_name"], $filePath);
		$this->update_kd_status($filePath);
		
	}
	
	private function update_kd_status($filePath)
	{
		
		set_time_limit(3000);
		
		$this->load->library("PHPExcel");//ci框架中引入excel类
		$PHPExcel = new PHPExcel();
		$PHPReader = new PHPExcel_Reader_Excel2007();
		if (!$PHPReader->canRead($filePath)) {
			$PHPReader = new PHPExcel_Reader_Excel5();
			if (!$PHPReader->canRead($filePath)) {
				re_json([], 1, '不是Excel');
				return;
			}
		}
		
		//$sheet = $PHPExcel->getActiveSheet();
		
		$PHPExcel = $PHPReader->load($filePath);
		$sheet = $PHPExcel->getSheet(0);                // 读取excel文件中的第一个工作表
		$allRow = $sheet->getHighestRow();            // 取得一共有多少行
		
		$where = [];
		
		for ($row = 2; $row <= $allRow; $row++) {                            //行数是以第2行开始
			
			$status = $sheet->getCell("C" . $row)->getValue();
			
			if (!in_array($status, ['OK', 'KO'])) {
				continue;
			}
			
			$data = [];
			$where['zt_sku'] = $sheet->getCell("A" . $row)->getValue();
			$data['status'] = $status == 'OK' ? 3 : 4;
			$data['sjtime'] = time();
			$this->db->where($where)->limit(1)->update("kd_product", $data);
		
		}
		
		unlink($filePath);            # 读完就删除，不保留上传的文件
		re_json([], 1, '导入成功');
		
	}
	
	
}
