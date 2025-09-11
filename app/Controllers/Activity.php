<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class Activity extends BaseController
{
    public function index()
    {
        if(has_permission('activityView'))
        {
            $data['title'] = 'Kegiatan';
            $data['open'] = 11;
            $data['active'] = 18;
            
            $db = \Config\Database::connect();
            $session = \Config\Services::session();
            
            $data['breadcrumbs'] = $db->query("SELECT name, href FROM menu WHERE id = ".$data['open']." OR id = ".$data['active']." ORDER BY parent_id ASC");
            
            $data['search'] = $session->get('searchActivityList');
            $data['start'] = $session->get('startActivityList');
            $data['length'] = $session->get('lengthActivityList');
            $data['orderCol'] = $session->get('orderColActivityList');
            $data['orderDir'] = $session->get('orderDirActivityList');
            
            return view('activity/index', $data);
        }
        else
        {
            return redirect()->back();
        }
    }

    public function activityList()
    {
        if(has_permission('activityView'))
        {
            $db = \Config\Database::connect();
            $session = \Config\Services::session();
            
            //Searching
            
            $search = $db->escapeString($this->request->getPost('search'));
            $session->set('searchActivityList', $search);
            
            $sSearch = '';
            
            if(isset($search) && $search != '')
            {
                $sSearch .= " AND (a.nama LIKE '%".$search."%' OR a.deskripsi LIKE '%".$search."%') ";
            }
            
            //Limit
            
            $start = $this->request->getPost("start");
            $session->set('startActivityList', $start);
            $length = $this->request->getPost("length");
            $session->set('lengthActivityList', $length);
            
            if (isset($start) && $start != '-1' )
            {
                $sLimit = " LIMIT ".intval($start).", ".intval($length);
            }
            
            //Ordering
            
            $orderCol = $this->request->getPost("order[0][column]");
            $session->set('orderColActivityList', $orderCol);
            $orderDir = $this->request->getPost("order[0][dir]");
            $session->set('orderDirActivityList', $orderDir);
            $columnName = $this->request->getPost("columns[".$orderCol."][name]");
            
            if (isset($columnName) && $columnName != '')
            {
                $sOrder = " ORDER BY ".$columnName." ".$orderDir." ";
            }
            else
            {
                $sOrder = " ORDER BY a.nama ASC, a.waktu_mulai DESC ";
            }
            
            //Table Row
            
            $s1 = "
                SELECT a.id, a.nama, a.waktu_mulai, a.waktu_selesai, b.nama AS nama_lab, c.nama AS nama_room, d.nama_sdm
                FROM activity a
                LEFT JOIN data_lab b ON(a.id_lab = b.id)
                LEFT JOIN data_room c ON(a.id_room = c.id)
                LEFT JOIN ref_dosen d ON(a.id_dosen = d.id)
                WHERE 1=1
            ";

            if(!in_groups('admin')) {
                $s1 .= " AND IF(EXISTS(SELECT e.id_pengelola FROM data_pengelola e WHERE e.id_lab = b.id AND e.id_pengelola = '".user_id()."'), 1, 0) = 1 ";
            }
            
            $s1 .= $sSearch;
            $s1 .= $sOrder;
            $s1 .= $sLimit;
        
            $sheet1 = $db->query($s1);
            
            $sheet_total = $db->query("SELECT a.id FROM activity a WHERE 1=1 ".$sSearch)->getNumRows();
        
            $result = array();
            $i=0;
            $no = (isset($start) ? $start + 1 : 1);
            foreach($sheet1->getResult() as $sheet)
            {
                $inventaris_button = '<button id="'.$sheet->id.'" class="btn btn-success inventaris" title="Inventaris"><i class="fa fa-folder"></i> Inventaris</button>';
                $rooms_button = '<button id="'.$sheet->id.'" class="btn btn-primary rooms" title="Ruangan"><i class="fa fa-hotel"></i> Ruangan</button>';
                
                if(has_permission('labEdit'))
                {
                    $edit_button = '<button id="'.$sheet->id.'" class="btn btn-info edit" title="edit"><i class="fa fa-edit"></i></button>';
                    $managers_button = '<button id="'.$sheet->id.'" class="btn btn-warning manager" title="Pengelola"><i class="fa fa-users"></i></button>';
                }
                else
                {
                    $edit_button = '';
                    $managers_button = '';
                }
                
                if(has_permission('labDelete'))
                {
                    $delete_button = '<button id="'.$sheet->id.'" class="btn btn-danger delete" title="hapus data lab '.$sheet->nama.'"><i class="fa fa-trash"></i></button>';
                }
                else
                {
                    $delete_button = '';
                }
                
                $result[$i]['0'] = $no;
                $result[$i]['1'] = $sheet->nama;
                $result[$i]['2'] = $sheet->nama_sdm;
                $result[$i]['3'] = $sheet->nama_lab;
                $result[$i]['4'] = $sheet->nama_room;
                $result[$i]['5'] = tgl_wkt($sheet->waktu_mulai) . ' - ' . tgl_wkt($sheet->waktu_selesai);
                $result[$i]['6'] = '
                    <div class="text-right">
                        '.$inventaris_button.' '.$rooms_button.' '.$managers_button.' '.$edit_button.' '.$delete_button.'
                        <script>
                        $(".edit").click(function(){var id = $(this).attr("id");window.location.assign("/data/labEdit/"+id);});
                        $(".delete").click(function() {var id = $(this).attr("id");var title = $(this).attr("title");
                            swal({title: "Apakah anda yakin akan meng"+title+"?", text: "Semua data akan dihapus dan tidak dapat dikembalikan!", icon: "warning",
                            buttons:{cancel: {visible: true, text : "Batal", className: "btn" },
                            confirm: {text : "Hapus", className : "btn btn-danger"}}}).then((willDelete) => {if (willDelete) {
                            $.ajax({url: "/data/labDelete", type: "POST", data: {"id":id}, cache: false});oTable.fnDraw();}});});
                        </script>
                    </div>
                ';
                $i++;
                $no++;
            }
            
            $data = $result;
            $results = array(
                "iTotalRecords" => ($sheet_total),
                "iTotalDisplayRecords" => ($sheet_total),
                "aaData"=>$data);
                
            echo json_encode($results);
        }
    }

    public function activityAdd()
    {
        if(has_permission('activityAdd'))
        {
            $data['title'] = 'Tambah Kegiatan';
            $data['open'] = 11;
            $data['active'] = 18;
            
            $db = \Config\Database::connect();
            
            $data['breadcrumbs'] = $db->query("SELECT name, href FROM menu WHERE id = ".$data['open']." OR id = ".$data['active']." ORDER BY parent_id ASC");
            
            if($this->request->getMethod() == 'POST')
            {
                $validation = \Config\Services::validation();
                
                $validation->setRules([
                    'nama' => ['label' => 'Nama', 'rules' => 'required']
                ]);
                
                $isDataValid = $validation->withRequest($this->request)->run();
                
                if($isDataValid)
                {
                    $post = $this->request->getPost();
                    $waktu_mulai = "{$post['tgl']} {$post['h_mulai']}:{$post['i_mulai']}:00";
                    $waktu_selesai = "{$post['tgl']} {$post['h_selesai']}:{$post['i_selesai']}:00";

                    $check = $db->query("
                        SELECT *
                        FROM activity
                        WHERE ('$waktu_mulai' >= waktu_mulai AND '$waktu_mulai' < waktu_selesai)
                        AND id_lab = 1
                        AND id_room = 2;
                    ");

                    if($check->getNumRows() > 0)
                    {
                        return redirect()->back()->withInput()->with('error', 'Jadwal bentrok, silahkan atur kembali waktu pelaksanaan');
                    }
                    else
                    {
                        $data = [
                            'id_lab'            => $post['id_lab'],
                            'id_room'           => $post['id_room'],
                            'id_dosen'          => $post['id_dosen'],
                            'nama'              => $post['nama'],
                            'deskripsi'         => $post['deskripsi'],
                            'waktu_mulai'       => $waktu_mulai,
                            'waktu_selesai'     => $waktu_selesai,
                            'added_by'          => user_id(),
                            'updated_by'        => user_id(),
                            'added_date'        => date('Y-m-d H:i:s'),
                            'updated_date'      => date('Y-m-d H:i:s')
                        ];
                        $db->table('activity')->insert($data);
                    }
                    
                    return redirect()->to('/activity')->with('message', 'Data telah berhasil ditambahkan!');
                }
                else
                {
                    return redirect()->back()->withInput()->with('errors', $validation->getErrors());
                }
            }

            $qLabs = "
                SELECT a.id, a.deskripsi AS nama
                FROM data_lab a
            ";

            if(!in_groups('admin')) {
                $qLabs .= "
                    WHERE IF(
                        EXISTS(
                            SELECT b.id_pengelola
                            FROM data_pengelola b
                            WHERE b.id_lab = a.id
                            AND b.id_pengelola = '".user_id()."'
                        ), 1, 0
                    ) = 1
                ";
            }
            
            $qLabs .= " ORDER BY nama ASC";
            
            $labs = $db->query($qLabs)->getResult();
            $dosen = $db->query("SELECT id, nama_sdm FROM ref_dosen")->getResult();

            $data['db'] = $db;
            $data['labs'] = $labs;
            $data['dosen'] = $dosen;
            
            return view('activity/activity_add', $data);
        }
        else
        {
            return redirect()->back();
        }
    }

    public function getRooms($id_lab)
    {
        $db = \Config\Database::connect();
        $rooms = $db->query("SELECT id, nama FROM data_room WHERE id_lab = $id_lab");
        $html = '<option value="">-- Pilih ruangan</option>';

        foreach($rooms->getResult() as $row) {
            $html .= '<option value="' . $row->id . '">' . $row->nama . '</option>';
        }

        echo $html;
    }
}
