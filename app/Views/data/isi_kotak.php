<!DOCTYPE html>
<html lang="en">
	<head>
		<?php echo view('partials/head'); ?>
		
		<link href="/assets/vendor/select2/select2.css" rel="stylesheet" />
		<link href="/assets/vendor/select2/select2-bootstrap.css" rel="stylesheet" />
	</head>
    <body data-background-color="bg3">
        <div id="wrapper">
			<?php echo view('partials/nav'); ?>

			<div class="main-panel animated fadeIn">
                <div class="content">
                    <div class="page-inner">
                        <div class="page-header">
                            <h1 class="page-title"><?php echo $title; ?></h1>
							<ul class="breadcrumbs">
								<li class="nav-home">
									<a href="/dashboard">
										<i class="flaticon-home"></i>
									</a>
								</li>
								<?php foreach($breadcrumbs->getResult() as $bc) { ?>
								
								<li class="separator">
									<i class="flaticon-right-arrow"></i>
								</li>
								<li class="nav-item">
									<a href="<?php echo $bc->href; ?>"><?php echo $bc->name; ?></a>
								</li>
								<?php } ?>
								<li class="separator">
									<i class="flaticon-right-arrow"></i>
								</li>
								<li class="nav-item">
									<?php echo $kotak->nama; ?>
								</li>
								<li class="separator">
									<i class="flaticon-right-arrow"></i>
								</li>
								<li class="nav-item">
									Isi Kotak
								</li>
								
							</ul>
                        </div>
						<?php if (session()->has('message')) : ?>
						
						<div class="alert alert-success">
							<h2><?php echo session('message') ?></h2>
						</div>
						<?php endif ?>
						
                        <div class="row">
                            <div class="col-md-12">
                                <div class="card">
									<div class="card-header row">
										<?php if(has_permission('inventarisAdd')) { ?>
										<div class="col-sm-2 mb-2">
											<button id="back" class="btn btn-block btn-danger btn-sm font-weight-bold back"><i class="fa fa-arrow-left mr-2"></i>Kembali</button>
										</div>
										<div class="col-sm-4 mb-2">
											<select class="form-control select2" name="id_inventaris" id="id_inventaris">
												<option value="">-- Pilih item</option>
											<?php
											$q = "
												SELECT a.id, a.nama, code_prefix, code_suffix, code_length
												FROM data_inventaris a
												LEFT JOIN data_lab b ON(a.id_lab = b.id)
												WHERE a.id_lab = " . $kotak->id_lab . "
												ORDER BY nama ASC
											";
											
											$labs = $db->query($q)->getResult();
											
											foreach ($labs as $row)
											{
												echo '
													<option value="' . $row->id .'">' . $row->nama . '</option>
												';
											}
											?>
											</select>
										</div>
										<div class="col-sm-4 mb-2">
											<input class="form-control form-control-sm" type="number" step="1" name="jml" id="jml" placeholder="Jumlah item">
										</div>
										<div class="col-sm-2 mb-2">
											<button id="add" class="btn btn-block btn-primary btn-sm font-weight-bold"><i class="fa fa-plus mr-2"></i>Tambah</button>
										</div>
										<?php } ?>
									</div>
                                    <div class="card-body">
										<script src="/assets/js/plugin/sweetalert/sweetalert.min.js"></script>
										<div class="table-responsive">
											<table class="table table-striped table-hover" id="table1" style="width:100%">
												<thead>
													<tr>
														<th><div class="pr-3">No</div></th>
														<th><div class="pr-3">Nama</div></th>
														<th><div class="pr-3">Jumlah</div></th>
														<th><div class="text-right pr-3">Aksi</div></th>
													</tr>
												</thead>
												<tbody></tbody>
											</table>
										</div>
                                    </div>
                                </div>
                                <!-- /.panel-body -->
                            </div>
                            <!-- /.panel -->
                        </div>
                        <!-- /.col-lg-12 -->
                    </div>
                </div>
				<?php echo view('partials/footer'); ?>
				
            </div>
        </div>
		
		<script src="/assets/js/core/jquery.3.2.1.min.js"></script>
		<script src="/assets/js/core/popper.min.js"></script>
		<script src="/assets/js/core/bootstrap.min.js"></script>
		<script src="/assets/js/plugin/jquery-ui-1.12.1.custom/jquery-ui.min.js"></script>
		<script src="/assets/js/plugin/jquery-ui-touch-punch/jquery.ui.touch-punch.min.js"></script>
		<script src="/assets/js/plugin/bootstrap-toggle/bootstrap-toggle.min.js"></script>
		<script src="/assets/js/plugin/jquery-scrollbar/jquery.scrollbar.min.js"></script>
		<script src="/assets/js/ready.min.js"></script>
		<script src="/assets/vendor/select2/select2.min.js"></script>
		
        <script src="/assets/js/plugin/datatables/datatables.min.js"></script>
		
        <script>
			$('#add').click(function(){
				$.ajax({
					type: "POST",
					url: '/data/isiKotakAdd',
					"data": {
						'id_kotak_penyimpanan' : <?php echo $id_kotak_penyimpanan; ?>,
						'id_inventaris' : function(){return $("#id_inventaris").val();},
						'jml' : function(){return $("#jml").val();}
					},
					beforeSend: function(){},
					dataType: "html",
					success: function(data){
						 oTable.fnStandingRedraw();
					}
				});
			});
			
			$.fn.dataTableExt.oApi.fnStandingRedraw = function(oSettings) {
				if(oSettings.oFeatures.bServerSide === false){
					var before = oSettings._iDisplayStart;
					oSettings.oApi._fnReDraw(oSettings);
					oSettings._iDisplayStart = before;
					oSettings.oApi._fnCalculateEnd(oSettings);
				}
				oSettings.oApi._fnDraw(oSettings);
			};
			
            var oTable = $("#table1").dataTable({
				"columnDefs": [
					{ "name": "", "targets": 0, "orderable": false },
					{ "name": "a.nama", "targets": 1 },
					{ "name": "a.jml", "targets": 2 }
				],
				"paging": false,
				"searching": true,
				"ordering": true,
				"order": [[<?php echo ((isset($orderCol) && $orderCol != '') ? $orderCol : 0)?>, "<?php echo ((isset($orderDir) && $orderDir != '') ? $orderDir : 'asc')?>"]],
				"bFilter" : false,							 
				"bLengthChange": true,
				"processing": true,
				"serverSide": true,
				"ajax": {
					"url": "/data/isiKotakList",
					"type": "POST",
					"data": {
						'id_kotak_penyimpanan' : <?php echo $id_kotak_penyimpanan; ?>
					}
				}
			});
			
			$(document).ready(function() {
				$('.select2').select2({
					theme: "bootstrap"
				});
			});

			$('.back').click(function(){
				parent.history.back();
				return false;
			});
        </script>
    </body>
</html>