<!DOCTYPE html>
<html lang="en">
	<head>
		<?php echo view('partials/head'); ?>
		
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
									<?php if(has_permission('labAdd')) { ?>
									<div class="card-header">
										<button id="add" class="btn btn-primary btn-sm font-weight-bold"><i class="fa fa-plus mr-2"></i>Tambah</button>
									</div>
									<?php } ?>
                                    <div class="card-body">
										<script src="/assets/js/plugin/sweetalert/sweetalert.min.js"></script>
										<div class="table-responsive">
											<table class="table table-striped table-hover" id="table1" style="width:100%">
												<thead>
													<tr>
														<th><div class="pr-3">No</div></th>
														<th><div class="pr-3">Nama Lab</div></th>
														<th><div class="pr-3">Deskripsi</div></th>
														<th><div class="pr-3">Lantai Awal</div></th>
														<th><div class="pr-3">Jml Lantai</div></th>
														<th><div class="pr-3">Jml Ruangan</div></th>
														<th><div class="pr-3 text-right"></div></th>
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
		
		<script src="/assets/js/plugin/datatables/datatables.min.js"></script>
		
        <script>
			$('#add').click(function(){
				window.location.assign('/data/labAdd')
			});
			
            var oTable = $("#table1").dataTable({
				"search": {
					"search": "<?php echo $search; ?>"
				},
				"columnDefs": [
					{ "name": "a.nama", "targets": 1 },
					{ "name": "a.deskripsi", "targets": 2 },
					{ "name": "a.lantai_awal", "targets": 3 },
					{ "name": "a.jml_lantai", "targets": 4 },
					{ "name": "jml_ruangan", "targets": 5 }
				],
				"pagingType": "numbers",
				"searching": true,
				"ordering": true,
				"order": [[<?php echo ((isset($orderCol) && $orderCol != '') ? $orderCol : 0)?>, "<?php echo ((isset($orderDir) && $orderDir != '') ? $orderDir : 'asc')?>"]],
				"iDisplayStart": <?php echo ((isset($start) && $start != '') ? $start : 0)?>,
				"iDisplayLength" : <?php echo ((isset($length) && $length != '') ? $length : 10)?>,
				"bFilter" : false,							 
				"bLengthChange": true,
				"processing": true,
				"serverSide": true,
				"ajax": {
					"url": "/data/labList",
					"type": "POST",
					"data": {
						'search': function(){return $("input[type=search]").val();}
					}
				}
			});
        </script>
    </body>
</html>