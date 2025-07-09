<?php
session_start();

include 'db_connection.php';  // Koneksi database

/**
 * Perform an LDAP search using comma seperated search strings
 *
 * @param string search string of search values
 */
function _ldap_simple_search($ds,$dn,$search) {
	$results = explode(';', $search);
	foreach($results as $key=>$result) {
		$results[$key] = '('.$result.')';
	}
	return _ldap_search($ds,$dn,$results);
}

/**
 * Perform an LDAP search
 *
 * @param array Search Filters (array of strings)
 * @param string DN Override
 * @return array Multidimensional array of results
 * @access public
 */

// Set zona waktu ke Indonesia (WIB)
date_default_timezone_set('Asia/Jakarta');

// Fungsi untuk mencatat aktivitas login
function log_activity($username, $status, $role, $aksi, $error_message = NULL) {
    global $conn;  // Gunakan koneksi yang sudah ada dari luar fungsi

    // Log untuk memastikan fungsi dipanggil
    error_log("log_activity dipanggil dengan username: $username");

    // Memastikan username ada di tabel users
    $user_check_query = "SELECT username FROM users WHERE username = '$username'";
    $user_check_result = $conn->query($user_check_query);

    if ($user_check_result->num_rows == 0) {
        // Jika username tidak ditemukan di tabel users
        error_log("Username $username tidak ditemukan di tabel users. Log tidak dicatat.");
        return false;
    }

    // Mendapatkan informasi IP address dan user agent
    $ip_address = $_SERVER['REMOTE_ADDR']; 
    $user_agent = $_SERVER['HTTP_USER_AGENT']; 

    // Mendapatkan timestamp dalam format Indonesia (WIB)
    $timestamp = date('Y-m-d H:i:s');  // Format tanggal dan jam Indonesia (WIB)
    
    // Query untuk mencatat log aktivitas ke dalam tabel log_activity
    $query = "INSERT INTO log_activity (timestamp, username, ip_address, user_agent, status, role, aksi, error_message) 
              VALUES ('$timestamp', '$username', '$ip_address', '$user_agent', '$status', '$role', '$aksi', '$error_message')";

    // Menambahkan pengecekan error pada query
    if ($conn->query($query) === TRUE) {
        error_log("Log aktivitas berhasil ditambahkan untuk username: $username");
        return true;
    } else {
        // Mencetak kesalahan jika query gagal
        error_log("Error inserting log: " . $conn->error);
        return false;
    }
}

 function _ldap_search($lconn, $dn, $filters) {
	$attributes = array ();

	foreach ($filters as $search_filter)
	{
		$search_result = @ldap_search($lconn, $dn, $search_filter);
		if ($search_result && ($count = @ldap_count_entries($lconn, $search_result)) > 0)
		{
			for ($i = 0; $i < $count; $i++)
			{
				$attributes[$i] = Array ();
				if (!$i) {
					$firstentry = @ldap_first_entry($lconn, $search_result);
				} else {
					$firstentry = @ldap_next_entry($lconn, $firstentry);
				}
				$attributes_array = @ldap_get_attributes($lconn, $firstentry); // load user-specified attributes
				// ldap returns an array of arrays, fit this into attributes result array
				foreach ($attributes_array as $ki => $ai)
				{
					if (is_array($ai))
					{
						$subcount = $ai['count'];
						$attributes[$i][$ki] = Array ();
						for ($k = 0; $k < $subcount; $k++) {
							$attributes[$i][$ki][$k] = $ai[$k];
						}
					}
				}
				$attributes[$i]['dn'] = @ldap_get_dn($lconn, $firstentry);
			}
		}
	}
	return $attributes;
}

$ldap_success = false;

// Konfigurasi LDAP
$CONFIG['ldap_host'] = 'pdc.yarsi.ac.id';
$CONFIG['ldap_port'] = '389';
$CONFIG['ldap_basedn'] = 'dc=yarsi,dc=ac,dc=id';
$CONFIG['ldap_users_ou'] = 'ou=Users,dc=yarsi,dc=ac,dc=id';
$CONFIG['ldap_groups_ou'] = 'ou=Groups,dc=yarsi,dc=ac,dc=id';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // Query untuk mencari username di database
    $query = "SELECT * FROM users WHERE username = '$username'";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $_SESSION['user_id'] = $user['id'];

        // Verifikasi password
        if (password_verify($password, $user['password'])) {
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['logged_in'] = true;

            // Log aktivitas login berhasil
            $status = 'Login Berhasil';
            $role = $user['role'];
            $aksi = 'Login';
            $error_message = NULL;  // Tidak ada kesalahan
            log_activity($username, $status, $role, $aksi, $error_message);

            // Menyimpan informasi tambahan berdasarkan role
            if ($user['role'] == 'Mahasiswa') {
                $mahasiswa_query = "SELECT * FROM mahasiswa WHERE user_id = '".$user['id']."'";
                $mahasiswa_result = $conn->query($mahasiswa_query);
                
                if ($mahasiswa_result->num_rows > 0) {
                    $mahasiswa_data = $mahasiswa_result->fetch_assoc();
                    $_SESSION['nama'] = $mahasiswa_data['nama'];
                    $_SESSION['npm'] = $mahasiswa_data['npm'];
                    $_SESSION['program_studi'] = $mahasiswa_data['program_studi'];
                    $_SESSION['tahun_angkatan'] = $mahasiswa_data['tahun_angkatan'];
                    $_SESSION['fakultas'] = $mahasiswa_data['fakultas'];
                    $_SESSION['id_kelompok'] = $mahasiswa_data['id_kelompok'];
                    $_SESSION['logged_in'] = true;

                }
            } elseif (($user['role'] == 'Tutor') || ($user['role'] == 'Dosen Pengampu')) {
                $mentor_query = "SELECT * FROM mentor WHERE user_id = '".$user['id']."'";
                $mentor_result = $conn->query($mentor_query);

                if ($mentor_result->num_rows > 0) {
                    $mentor_data = $mentor_result->fetch_assoc();
                    $_SESSION['nama'] = $mentor_data['nama'];
                }
            }

            // Redirect berdasarkan role dan status login
            if ($user['role'] == 'Admin') {
                header("Location: /Entree/admin/dashboard");
            } elseif ($user['first_login'] == 1) {
                if ($user['role'] == 'Mahasiswa') {
                    header("Location: /Entree/lengkapi_data_mahasiswa");
                } elseif ($user['role'] == 'Tutor') {
                    header("Location: /Entree/lengkapi_data_mentor");
                }
            } else {
                if ($user['role'] == 'Mahasiswa') {
                    header("Location: /Entree/mahasiswa/dashboard");
                } elseif ($user['role'] == 'Tutor' || $user['role'] == 'Dosen Pengampu') {
                    header("Location: /Entree/mentor/dashboard");
                }
            }
            exit;
        } else {
            $_SESSION['error'] = "Password salah!";

            // Log aktivitas login gagal (salah password)
            $status = 'Login Gagal';
            $role = 'Unknown';  // Role tidak diketahui karena login gagal
            $aksi = 'Login';
            $error_message = "Password salah";  // Pesan kesalahan
            log_activity($username, $status, $role, $aksi, $error_message);

            header("Location: /Entree/login");
        }
    } else {
        $_SESSION['error'] = "Username tidak ditemukan!";

        // Log aktivitas login gagal (username tidak ditemukan)
        $status = 'Login Gagal';
        $role = 'Unknown';  // Role tidak diketahui karena login gagal
        $aksi = 'Login';
        $error_message = "Username tidak ditemukan";  // Pesan kesalahan
        log_activity($username, $status, $role, $aksi, $error_message);

        header("Location: /Entree/login");
    }
} else {
//     echo "Metode pengiriman tidak valid.";
// }
// 	// $uname = addslashes($_POST['username']);
// 	// $passwd = addslashes($_POST['password']);
// 	// $ds=ldap_connect($CONFIG['ldap_host'],$CONFIG['ldap_port']);  // must be a valid LDAP server!
// 	// if ($ds) {
//     //     $con = ldap_bind($ds);
//     //     if($con) {
//     //         $binddata = _ldap_simple_search($ds,$CONFIG['ldap_basedn'],'uid='.$uname);
//     //         if(isset($binddata[0]) && isset($binddata[0]['dn'])) {
//     //             // Verify Users Credentials
//     //             $success = @ldap_bind($ds, $binddata[0]['dn'], $passwd);
                
//     //             if ($success) {
//     //                 $ldap_success = true;
//     //             } else {
//     //                 $_SESSION['error'] = "Password tidak valid!";								
//     //                 $ldap_success = false;
//     //                 $status = 'Login Gagal';
//     //                 $role = 'Unknown';  // Role tidak diketahui karena login gagal
//     //                 $aksi = 'Login';
//     //                 $error_message = "Password salah";  // Pesan kesalahan
//     //                 log_activity($username, $status, $role, $aksi, $error_message);
//     //                 header("Location: /Entree/login");
//     //             }
//     //         } else {
//     //             $_SESSION['error'] = "Username tidak ditemukan!";
//     //             $ldap_success = false;
//     //             $status = 'Login Gagal';
//     //             $role = 'Unknown';  // Role tidak diketahui karena login gagal
//     //             $aksi = 'Login';
//     //             $error_message = "Username tidak ditemukan";  // Pesan kesalahan
//     //             log_activity($username, $status, $role, $aksi, $error_message);
//     //             header("Location: /Entree/login");
//     //         }
//     //     } else {
//     //         $_SESSION['error'] = 'Gagal melakukan autentikasi ke server LDAP.';
//     //         $ldap_success = false;
//     //     }
        
//     //     //echo "Closing connection";
//     //     ldap_close($ds);
// 	} else {
		$msgz = 'Gagal melakukan koneksi ke server LDAP.<br>';
		$ldap_success = false;
	}

if ($ldap_success) {
    // Regenerasi ID session untuk mencegah tab berbagi sesi yang sama
    session_regenerate_id();

    // Mendapatkan informasi dari LDAP
    $nama = $binddata[0]['displayName'][0];
    $npm = $binddata[0]['description'][0];
    $role = $binddata[0]['title'][0];
    $contact = $binddata[0]['telephoneNumber'][0];
    $street = isset($binddata[0]['street'][0]) ? $binddata[0]['street'][0] : null;

    $_SESSION['username'] = $uname;
    $_SESSION['displayName'] = $nama;
    $_SESSION['npm'] = $npm;
    $_SESSION['role'] = $role;
    $_SESSION['contact'] = $contact;
    $_SESSION['street'] = $street;

    // Cek apakah user sudah ada di database
    $check_user_query = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($check_user_query);
    $stmt->bind_param("s", $uname);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        // Jika belum ada, tambahkan user ke database
        $insert_user_query = "INSERT INTO users (username, password, role, first_login) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_user_query);
        $hashed_password = password_hash($passwd, PASSWORD_BCRYPT);
        $first_login = 1; 

        // Menyesuaikan role berdasarkan session
        $role_in_db = ($role == 'M') ? 'Mahasiswa' : (($role == 'D') ? 'Tutor' : 'Unknown');
        
        $stmt->bind_param("sssi", $uname, $hashed_password, $role_in_db, $first_login);
        $stmt->execute();
    }

    // Cek status first_login
    $check_user_query = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($check_user_query);
    $stmt->bind_param("s", $uname);
    $stmt->execute();
    $user_result = $stmt->get_result();
    $user = $user_result->fetch_assoc();

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['logged_in'] = true;

    // Redirect berdasarkan role dan first_login
    if ($user['role'] == 'Admin') {
        header("Location: /Entree/admin/dashboard");
    } elseif ($user['first_login'] == 1) {
        if ($user['role'] == 'Mahasiswa') {
            header("Location: /Entree/lengkapi_data_mahasiswa");
        } elseif ($user['role'] == 'Tutor') {
            header("Location: /Entree/lengkapi_data_mentor");
        }
    } else {
        if ($user['role'] == 'Mahasiswa') {
            header("Location: /Entree/mahasiswa/dashboard");
        } elseif ($user['role'] == 'Tutor' || $user['role'] == 'Dosen Pengampu') {
            header("Location: /Entree/mentor/dashboard");
        }
    }
    exit;
}
?>