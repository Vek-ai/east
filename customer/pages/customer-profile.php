<?php
include '../includes/dbconn.php';

if (!isset($_SESSION['userid'])) {
    header("Location: login.php");
    exit();
}

global $currentUser;

$userId = $_SESSION['userid'];

$sql = "SELECT * FROM customer WHERE customer_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $currentUser = $result->fetch_assoc();
} else {
    $currentUser = null;
}

$stmt->close();
$conn->close();

$firstName = isset($currentUser['customer_first_name']) ? htmlspecialchars($currentUser['customer_first_name']) : 'First Name';
$lastName = isset($currentUser['customer_last_name']) ? htmlspecialchars($currentUser['customer_last_name']) : 'Last Name';
?>

        <div class="container-fluid">
          <div class="font-weight-medium shadow-none position-relative overflow-hidden mb-7">
            <div class="card-body px-0">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <h4 class="font-weight-medium fs-14 mb-0">User Profile</h4>
                  <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                      <li class="breadcrumb-item">
                        <a class="text-muted text-decoration-none" href="">Home
                        </a>
                      </li>
                      <li class="breadcrumb-item text-muted" aria-current="page">User Profile</li>
                    </ol>
                  </nav>
                </div>
                <div>
                  <div class="d-sm-flex d-none gap-3 no-block justify-content-end align-items-center">
                    <div class="d-flex gap-2">
                      <div class="">
                        <small>This Month</small>
                        <h4 class="text-primary mb-0 ">$58,256</h4>
                      </div>
                      <div class="">
                        <div class="breadbar"></div>
                      </div>
                    </div>
                    <div class="d-flex gap-2">
                      <div class="">
                        <small>Last Month</small>
                        <h4 class="text-secondary mb-0 ">$58,256</h4>
                      </div>
                      <div class="">
                        <div class="breadbar2"></div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="card overflow-hidden">
            <div class="card-body p-0">
              <img src="../../assets/images/backgrounds/profilebg.jpg" alt="materialpro-img" class="img-fluid">
              <div class="row align-items-center">
                <div class="col-lg-4 order-lg-1 order-2">
                  <div class="d-flex align-items-center justify-content-around m-4">
                    <div class="text-center">
                      <i class="ti ti-file-description fs-6 d-block mb-2"></i>
                      <h4 class="mb-0 fw-semibold lh-1">938</h4>
                      <p class="mb-0 ">Posts</p>
                    </div>
                    <div class="text-center">
                      <i class="ti ti-user-circle fs-6 d-block mb-2"></i>
                      <h4 class="mb-0 fw-semibold lh-1">3,586</h4>
                      <p class="mb-0 ">Followers</p>
                    </div>
                    <div class="text-center">
                      <i class="ti ti-user-check fs-6 d-block mb-2"></i>
                      <h4 class="mb-0 fw-semibold lh-1">2,659</h4>
                      <p class="mb-0 ">Following</p>
                    </div>
                  </div>
                </div>
                <div class="col-lg-4 mt-n3 order-lg-2 order-1">
                  <div class="mt-n5">
                    <div class="d-flex align-items-center justify-content-center mb-2">
                      <div class="d-flex align-items-center justify-content-center round-110">
                        <div class="border border-4 border-white d-flex align-items-center justify-content-center rounded-circle overflow-hidden round-100">
                          <img src="../../assets/images/profile/user-2.jpg" alt="materialpro-img" class="w-100 h-100">
                        </div>
                      </div>
                    </div>
                    <div class="text-center">
                      <h5 class="mb-0"><h5 class="mb-1 fs-4"><?php echo $firstName . ' ' . $lastName; ?></h5></h5>
                      <p class="mb-0">Designer</p>
                    </div>
                  </div>
                </div>
                <div class="col-lg-4 order-last">
                  <ul class="list-unstyled d-flex align-items-center justify-content-center justify-content-lg-end my-3 mx-4 pe-4 gap-3">
                    <li>
                      <a class="d-flex align-items-center justify-content-center btn btn-primary p-2 fs-4 rounded-circle" href="javascript:void(0)" width="30" height="30">
                        <i class="ti ti-brand-facebook"></i>
                      </a>
                    </li>
                    <li>
                      <a class="btn btn-secondary d-flex align-items-center justify-content-center p-2 fs-4 rounded-circle" href="javascript:void(0)">
                        <i class="ti ti-brand-dribbble"></i>
                      </a>
                    </li>
                    <li>
                      <a class="btn btn-danger d-flex align-items-center justify-content-center p-2 fs-4 rounded-circle" href="javascript:void(0)">
                        <i class="ti ti-brand-youtube"></i>
                      </a>
                    </li>
                    <li>
                      <button class="btn btn-primary text-nowrap">Add To Story</button>
                    </li>
                  </ul>
                </div>
              </div>
              <ul class="nav nav-pills user-profile-tab justify-content-end mt-2 bg-primary-subtle rounded-2 rounded-top-0" id="pills-tab" role="tablist">
                <li class="nav-item" role="presentation">
                  <button class="nav-link active hstack gap-2 rounded-0 fs-12 py-6" id="pills-profile-tab" data-bs-toggle="pill" data-bs-target="#pills-profile" type="button" role="tab" aria-controls="pills-profile" aria-selected="true">
                    <i class="ti ti-user-circle fs-5"></i>
                    <span class="d-none d-md-block">Profile</span>
                  </button>
                </li>
                <li class="nav-item" role="presentation">
                  <button class="nav-link hstack gap-2 rounded-0 fs-12 py-6" id="pills-followers-tab" data-bs-toggle="pill" data-bs-target="#pills-followers" type="button" role="tab" aria-controls="pills-followers" aria-selected="false">
                    <i class="ti ti-heart fs-5"></i>
                    <span class="d-none d-md-block">Followers</span>
                  </button>
                </li>
                <li class="nav-item" role="presentation">
                  <button class="nav-link hstack gap-2 rounded-0 fs-12 py-6" id="pills-friends-tab" data-bs-toggle="pill" data-bs-target="#pills-friends" type="button" role="tab" aria-controls="pills-friends" aria-selected="false">
                    <i class="ti ti-user-circle fs-5"></i>
                    <span class="d-none d-md-block">Friends</span>
                  </button>
                </li>
                <li class="nav-item" role="presentation">
                  <button class="nav-link hstack gap-2 rounded-0 fs-12 py-6" id="pills-gallery-tab" data-bs-toggle="pill" data-bs-target="#pills-gallery" type="button" role="tab" aria-controls="pills-gallery" aria-selected="false">
                    <i class="ti ti-photo-plus fs-5"></i>
                    <span class="d-none d-md-block">Gallery</span>
                  </button>
                </li>
              </ul>
            </div>
          </div>
          <div class="tab-content" id="pills-tabContent">
            <div class="tab-pane fade show active" id="pills-profile" role="tabpanel" aria-labelledby="pills-profile-tab" tabindex="0">
              <div class="row">
                <div class="col-lg-4">
                  <div class="card shadow-none border">
                    <div class="card-body">
                      <h4 class="mb-3">Introduction</h4>
                      <p class="card-subtitle">Hello, I am <h5 class="mb-1 fs-4"><?php echo $firstName . ' ' . $lastName; ?></h5>. I love making websites and graphics. Lorem
                        ipsum dolor sit amet,
                        consectetur adipiscing elit.</p>
                      <div class="vstack gap-3 mt-4">
                        <div class="hstack gap-6">
                          <i class="ti ti-briefcase text-dark fs-6"></i>
                          <h6 class=" mb-0">Sir, P P Institute Of Science</h6>
                        </div>
                        <div class="hstack gap-6">
                          <i class="ti ti-mail text-dark fs-6"></i>
                          <h6 class=" mb-0">markrarn@wrappixel.com</h6>
                        </div>
                        <div class="hstack gap-6">
                          <i class="ti ti-device-desktop text-dark fs-6"></i>
                          <h6 class=" mb-0">www.xyz.com</h6>
                        </div>
                        <div class="hstack gap-6">
                          <i class="ti ti-map-pin text-dark fs-6"></i>
                          <h6 class=" mb-0">Newyork, USA - 100001</h6>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="card shadow-none border">
                    <div class="card-body">
                      <h4 class="fw-semibold mb-3">Photos</h4>
                      <div class="row">
                        <div class="col-4">
                          <img src="../../assets/images/profile/user-2.jpg" alt="materialpro-img" class="rounded-1 img-fluid mb-9">
                        </div>
                        <div class="col-4">
                          <img src="../../assets/images/profile/user-2.jpg" alt="materialpro-img" class="rounded-1 img-fluid mb-9">
                        </div>
                        <div class="col-4">
                          <img src="../../assets/images/profile/user-3.jpg" alt="materialpro-img" class="rounded-1 img-fluid mb-9">
                        </div>
                        <div class="col-4">
                          <img src="../../assets/images/profile/user-4.jpg" alt="materialpro-img" class="rounded-1 img-fluid mb-9">
                        </div>
                        <div class="col-4">
                          <img src="../../assets/images/profile/user-5.jpg" alt="materialpro-img" class="rounded-1 img-fluid mb-9">
                        </div>
                        <div class="col-4">
                          <img src="../../assets/images/profile/user-6.jpg" alt="materialpro-img" class="rounded-1 img-fluid mb-9">
                        </div>
                        <div class="col-4">
                          <img src="../../assets/images/profile/user-7.jpg" alt="materialpro-img" class="rounded-1 img-fluid mb-6">
                        </div>
                        <div class="col-4">
                          <img src="../../assets/images/profile/user-8.jpg" alt="materialpro-img" class="rounded-1 img-fluid mb-6">
                        </div>
                        <div class="col-4">
                          <img src="../../assets/images/profile/user-2.jpg" alt="materialpro-img" class="rounded-1 img-fluid mb-6">
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-lg-8">
                  <div class="card shadow-none border">
                    <div class="card-body">
                      <div class="form-floating mb-3">
                        <textarea class="form-control h-140" placeholder="Leave a comment here" id="floatingTextarea2"></textarea>
                        <label for="floatingTextarea2">Share your thoughts</label>
                      </div>
                      <div class="d-flex align-items-center gap-6 flex-wrap">
                        <a class="d-flex align-items-center round-32 justify-content-center btn btn-primary rounded-circle p-0" href="javascript:void(0)">
                          <i class="ti ti-photo"></i>
                        </a>
                        <a href="javascript:void(0)" class="text-dark link-primary pe-3 py-2">Photo / Video</a>

                        <a class="d-flex align-items-center round-32 justify-content-center btn btn-secondary rounded-circle p-0" href="javascript:void(0)">
                          <i class="ti ti-notebook"></i>
                        </a>
                        <a href="javascript:void(0)" class="text-dark link-secondary pe-3 py-2">Article</a>


                        <button class="btn btn-primary ms-auto">Post</button>
                      </div>
                    </div>
                  </div>
                  <div class="card">
                    <div class="card-body border-bottom">
                      <div class="d-flex align-items-center gap-6 flex-wrap">
                        <img src="../../assets/images/profile/user-2.jpg" alt="materialpro-img" class="rounded-circle" width="40" height="40">
                        <h6 class="mb-0"><h5 class="mb-1 fs-4"><?php echo $firstName . ' ' . $lastName; ?></h5></h6>
                        <span class="fs-2 hstack gap-2">
                          <span class="round-10 text-bg-light rounded-circle d-inline-block"></span> 15 min
                          ago
                        </span>
                      </div>
                      <p class="text-dark my-3">
                        Nu kek vuzkibsu mooruno ejepogojo uzjon gag fa ezik disan he nah. Wij wo pevhij tumbug rohsa
                        ahpi ujisapse lo vap labkez eddu suk.
                      </p>
                      <img src="../../assets/images/products/s1.jpg" alt="materialpro-img" height="360" class="rounded-4 w-100 object-fit-cover">
                      <div class="d-flex align-items-center my-3">
                        <div class="d-flex align-items-center gap-2">
                          <a class="round-32 rounded-circle btn btn-primary p-0 hstack justify-content-center" href="javascript:void(0)" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Like">
                            <i class="ti ti-thumb-up"></i>
                          </a>
                          <span class="text-dark fw-semibold">67</span>
                        </div>
                        <div class="d-flex align-items-center gap-2 ms-4">
                          <a class="round-32 rounded-circle btn btn-secondary p-0 hstack justify-content-center" href="javascript:void(0)" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Comment">
                            <i class="ti ti-message-2"></i>
                          </a>
                          <span class="text-dark fw-semibold">2</span>
                        </div>
                        <a class="text-dark ms-auto d-flex align-items-center justify-content-center bg-transparent p-2 fs-4 rounded-circle" href="javascript:void(0)" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Share">
                          <i class="ti ti-share"></i>
                        </a>
                      </div>
                      <div class="position-relative">
                        <div class="p-4 rounded-2 text-bg-light mb-3">
                          <div class="d-flex align-items-center gap-6 flex-wrap">
                            <img src="../../assets/images/profile/user-3.jpg" alt="materialpro-img" class="rounded-circle" width="33" height="33">
                            <h6 class="mb-0">Deran Mac</h6>
                            <span class="fs-2">
                              <span class="p-1 text-bg-muted rounded-circle d-inline-block"></span> 8 min ago
                            </span>
                          </div>
                          <p class="my-3">Lufo zizrap iwofapsuk pusar luc jodawbac zi op uvezojroj duwage vuhzoc ja
                            vawdud le furhez siva
                            fikavu ineloh. Zot afokoge si mucuve hoikpaf adzuk zileuda falohfek zoije fuka udune lub
                            annajor gazo
                            conis sufur gu.
                          </p>
                          <div class="d-flex align-items-center">
                            <div class="d-flex align-items-center gap-2">
                              <a class="round-32 rounded-circle btn btn-primary p-0 hstack justify-content-center" href="javascript:void(0)" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Like">
                                <i class="ti ti-thumb-up"></i>
                              </a>
                              <span class="text-dark fw-semibold">55</span>
                            </div>
                            <div class="d-flex align-items-center gap-2 ms-4">
                              <a class="round-32 rounded-circle btn btn-secondary p-0 hstack justify-content-center" href="javascript:void(0)" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Reply">
                                <i class="ti ti-arrow-back-up"></i>
                              </a>
                              <span class="text-dark fw-semibold">0</span>
                            </div>
                          </div>
                        </div>
                        <div class="p-4 rounded-2 text-bg-light mb-3">
                          <div class="d-flex align-items-center gap-6 flex-wrap">
                            <img src="../../assets/images/profile/user-4.jpg" alt="materialpro-img" class="rounded-circle" width="33" height="33">
                            <h6 class="mb-0">Daisy Wilson</h6>
                            <span class="fs-2">
                              <span class="p-1 text-bg-muted rounded-circle d-inline-block"></span> 5
                              min
                              ago
                            </span>
                          </div>
                          <p class="my-3">
                            Zumankeg ba lah lew ipep tino tugjekoj hosih fazjid wotmila durmuri buf hi sigapolu joit
                            ebmi joge vo.
                            Horemo vogo hat na ejednu sarta afaamraz zi cunidce peroido suvan podene igneve.
                          </p>
                          <div class="d-flex align-items-center">
                            <div class="d-flex align-items-center gap-2">
                              <a class="round-32 rounded-circle btn btn-primary p-0 hstack justify-content-center" href="javascript:void(0)" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Like">
                                <i class="ti ti-thumb-up"></i>
                              </a>
                              <span class="text-dark fw-semibold">68</span>
                            </div>
                            <div class="d-flex align-items-center gap-2 ms-4">
                              <a class="round-32 rounded-circle btn btn-secondary p-0 hstack justify-content-center" href="javascript:void(0)" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Reply">
                                <i class="ti ti-arrow-back-up"></i>
                              </a>
                              <span class="text-dark fw-semibold">1</span>
                            </div>
                          </div>
                        </div>
                        <div class="p-4 rounded-2 text-bg-light ms-7">
                          <div class="d-flex align-items-center gap-6 flex-wrap">
                            <img src="../../assets/images/profile/user-5.jpg" alt="materialpro-img" class="rounded-circle" width="40" height="40">
                            <h6 class="mb-0">Carry minati</h6>
                            <span class="fs-2">
                              <span class="p-1 text-bg-muted rounded-circle d-inline-block"></span>
                              just
                              now
                            </span>
                          </div>
                          <p class="my-3">
                            Olte ni somvukab ugura ovaobeco hakgoc miha peztajo tawosu udbacas kismakin hi. Dej
                            zetfamu cevufi sokbid bud mun soimeuha pokahram vehurpar keecris pepab voegmud
                            zundafhef hej pe.
                          </p>
                        </div>
                      </div>
                    </div>
                    <div class="d-flex align-items-center gap-6 flex-wrap p-3 flex-lg-nowrap">
                      <img src="../../assets/images/profile/user-2.jpg" alt="materialpro-img" class="rounded-circle" width="33" height="33">
                      <input type="text" class="form-control py-8" id="exampleInputtext" aria-describedby="textHelp" placeholder="Comment">
                      <button class="btn btn-primary">Comment</button>
                    </div>
                  </div>
                  <div class="card">
                    <div class="card-body border-bottom">
                      <div class="d-flex align-items-center gap-6 flex-wrap">
                        <img src="../../assets/images/profile/user-5.jpg" alt="materialpro-img" class="rounded-circle" width="40" height="40">
                        <h6 class="mb-0">Carry Minati</h6>
                        <span class="fs-2">
                          <span class="p-1 text-bg-light rounded-circle d-inline-block"></span>
                          now
                        </span>
                      </div>
                      <p class="text-dark my-3">
                        Pucnus taw set babu lasufot lawdebuw nem ig bopnub notavfe pe ranlu dijsan liwfekaj lo az. Dom
                        giat gu
                        sehiosi bikelu lo eb uwrerej bih woppoawi wijdiola iknem hih suzega gojmev kir rigoj.
                      </p>
                      <div class="d-flex align-items-center">
                        <div class="d-flex align-items-center gap-2">
                          <a class="round-32 rounded-circle btn btn-primary p-0 hstack justify-content-center" href="javascript:void(0)" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Like">
                            <i class="ti ti-thumb-up"></i>
                          </a>
                          <span class="text-dark fw-semibold">1</span>
                        </div>
                        <div class="d-flex align-items-center gap-2 ms-4">
                          <a class="round-32 rounded-circle btn btn-secondary p-0 hstack justify-content-center" href="javascript:void(0)" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Comment">
                            <i class="ti ti-message-2"></i>
                          </a>
                          <span class="text-dark fw-semibold">0</span>
                        </div>
                        <a class="text-dark ms-auto d-flex align-items-center justify-content-center bg-transparent p-2 fs-4 rounded-circle" href="javascript:void(0)" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Share">
                          <i class="ti ti-share"></i>
                        </a>
                      </div>
                    </div>
                    <div class="d-flex align-items-center gap-6 flex-wrap p-3 flex-lg-nowrap">
                      <img src="../../assets/images/profile/user-2.jpg" alt="materialpro-img" class="rounded-circle" width="33" height="33">
                      <input type="text" class="form-control py-8" id="exampleInputtext1" aria-describedby="textHelp" placeholder="Comment">
                      <button class="btn btn-primary">Comment</button>
                    </div>
                  </div>
                  <div class="card">
                    <div class="card-body border-bottom">
                      <div class="d-flex align-items-center gap-6 flex-wrap">
                        <img src="../../assets/images/profile/user-2.jpg" alt="materialpro-img" class="rounded-circle" width="40" height="40">
                        <h6 class="mb-0">Genelia Desouza</h6>
                        <span class="fs-2">
                          <span class="p-1 text-bg-light rounded-circle d-inline-block"></span> 15 min
                          ago
                        </span>
                      </div>
                      <p class="text-dark my-3">
                        Faco kiswuoti mucurvi juokomo fobgi aze huweik zazjofefa kuujer talmoc li niczot lohejbo vozev
                        zi huto. Ju
                        tupma uwujate bevolkoh hob munuap lirec zak ja li hotlanu pigtunu.
                      </p>
                      <div class="row">
                        <div class="col-sm-6">
                          <img src="../../assets/images/products/s2.jpg" alt="materialpro-img" class="img-fluid rounded-4 mb-3 mb-sm-0">
                        </div>
                        <div class="col-sm-6">
                          <img src="../../assets/images/products/s4.jpg" alt="materialpro-img" class="img-fluid rounded-4">
                        </div>
                      </div>
                      <div class="d-flex align-items-center my-3">
                        <div class="d-flex align-items-center gap-2">
                          <a class="text-dark d-flex align-items-center justify-content-center bg-light p-2 fs-4 rounded-circle" href="javascript:void(0)" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Like">
                            <i class="ti ti-thumb-up"></i>
                          </a>
                          <span class="text-dark fw-semibold">320</span>
                        </div>
                        <div class="d-flex align-items-center gap-2 ms-4">
                          <a class="round-32 rounded-circle btn btn-secondary p-0 hstack justify-content-center" href="javascript:void(0)" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Comment">
                            <i class="ti ti-message-2"></i>
                          </a>
                          <span class="text-dark fw-semibold">1</span>
                        </div>
                        <a class="text-dark ms-auto d-flex align-items-center justify-content-center bg-transparent p-2 fs-4 rounded-circle" href="javascript:void(0)" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Share">
                          <i class="ti ti-share"></i>
                        </a>
                      </div>
                      <div class="p-4 rounded-2 text-bg-light">
                        <div class="d-flex align-items-center gap-6 flex-wrap">
                          <img src="../../assets/images/profile/user-3.jpg" alt="materialpro-img" class="rounded-circle" width="33" height="33">
                          <h6 class="mb-0">Ritesh Deshmukh</h6>
                          <span class="fs-2">
                            <span class="p-1 text-bg-muted rounded-circle d-inline-block"></span> 15
                            min
                            ago
                          </span>
                        </div>
                        <p class="my-3">
                          Hintib cojno riv ze heb cipcep fij wo tufinpu bephekdab infule pajnaji. Jiran goetimip muovo
                          go en
                          gaga zeljomim hozlu lezuvi ehkapod dec bifoom hag dootasac odo luvgit ti ella.
                        </p>
                        <div class="d-flex align-items-center">
                          <div class="d-flex align-items-center gap-2">
                            <a class="round-32 rounded-circle btn btn-primary p-0 hstack justify-content-center" href="javascript:void(0)" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Like">
                              <i class="ti ti-thumb-up"></i>
                            </a>
                            <span class="text-dark fw-semibold">65</span>
                          </div>
                          <div class="d-flex align-items-center gap-2 ms-4">
                            <a class="round-32 rounded-circle btn btn-secondary p-0 hstack justify-content-center" href="javascript:void(0)" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Reply">
                              <i class="ti ti-arrow-back-up"></i>
                            </a>
                            <span class="text-dark fw-semibold">0</span>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="d-flex align-items-center gap-6 flex-wrap p-3 flex-lg-nowrap">
                      <img src="../../assets/images/profile/user-2.jpg" alt="materialpro-img" class="rounded-circle" width="33" height="33">
                      <input type="text" class="form-control py-8" id="exampleInputtext2" aria-describedby="textHelp" placeholder="Comment">
                      <button class="btn btn-primary">Comment</button>
                    </div>
                  </div>
                  <div class="card">
                    <div class="card-body border-bottom">
                      <div class="d-flex align-items-center gap-6 flex-wrap">
                        <img src="../../assets/images/profile/user-2.jpg" alt="materialpro-img" class="rounded-circle" width="40" height="40">
                        <h6 class="mb-0"><h5 class="mb-1 fs-4"><?php echo $firstName . ' ' . $lastName; ?></h5></h6>
                        <span class="fs-2">
                          <span class="p-1 text-bg-light rounded-circle d-inline-block"></span> 15 min
                          ago
                        </span>
                      </div>
                      <p class="text-dark my-3">
                        Faco kiswuoti mucurvi juokomo fobgi aze huweik zazjofefa kuujer talmoc li niczot lohejbo vozev
                        zi huto. Ju
                        tupma uwujate bevolkoh hob munuap lirec zak ja li hotlanu pigtunu.
                      </p>
                      <iframe class="rounded-4 border border-2 mb-3 h-300" src="https://www.youtube.com/embed/d1-FRj20WBE" frameborder="0" width="100%"></iframe>
                      <div class="d-flex align-items-center">
                        <div class="d-flex align-items-center gap-2">
                          <a class="round-32 rounded-circle btn btn-primary p-0 hstack justify-content-center" href="javascript:void(0)" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Like">
                            <i class="ti ti-thumb-up"></i>
                          </a>
                          <span class="text-dark fw-semibold">129</span>
                        </div>
                        <div class="d-flex align-items-center gap-2 ms-4">
                          <a class="round-32 rounded-circle btn btn-secondary p-0 hstack justify-content-center" href="javascript:void(0)" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Comment">
                            <i class="ti ti-message-2"></i>
                          </a>
                          <span class="text-dark fw-semibold">0</span>
                        </div>
                        <a class="text-dark ms-auto d-flex align-items-center justify-content-center bg-transparent p-2 fs-4 rounded-circle" href="javascript:void(0)" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Share">
                          <i class="ti ti-share"></i>
                        </a>
                      </div>
                    </div>
                    <div class="d-flex align-items-center gap-6 flex-wrap p-3 flex-lg-nowrap">
                      <img src="../../assets/images/profile/user-2.jpg" alt="materialpro-img" class="rounded-circle" width="33" height="33">
                      <input type="text" class="form-control py-8" id="exampleInputtext3" aria-describedby="textHelp" placeholder="Comment">
                      <button class="btn btn-primary">Comment</button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="tab-pane fade" id="pills-followers" role="tabpanel" aria-labelledby="pills-followers-tab" tabindex="0">
              <div class="d-sm-flex align-items-center justify-content-between mt-3 mb-4">
                <h3 class="mb-3 mb-sm-0 fw-semibold d-flex align-items-center">Followers <span class="badge text-bg-secondary fs-2 rounded-4 py-1 px-2 ms-2">20</span>
                </h3>
                <form class="position-relative">
                  <input type="text" class="form-control search-chat py-2 ps-5" id="text-srh" placeholder="Search Followers">
                  <i class="ti ti-search position-absolute top-50 start-0 translate-middle-y text-dark ms-3"></i>
                </form>
              </div>
              <div class="row">
                <div class=" col-md-6 col-xl-4">
                  <div class="card">
                    <div class="card-body p-4 d-flex align-items-center gap-6 flex-wrap">
                      <img src="../../assets/images/profile/user-8.jpg" alt="materialpro-img" class="rounded-circle" width="40" height="40">
                      <div>
                        <h5 class="fw-semibold mb-0">Betty Adams</h5>
                        <span class="fs-2 d-flex align-items-center">
                          <i class="ti ti-map-pin text-dark fs-3 me-1"></i>Sint Maarten
                        </span>
                      </div>
                      <button class="btn btn-outline-primary py-1 px-2 ms-auto">Follow</button>
                    </div>
                  </div>
                </div>
                <div class=" col-md-6 col-xl-4">
                  <div class="card">
                    <div class="card-body p-4 d-flex align-items-center gap-6 flex-wrap">
                      <img src="../../assets/images/profile/user-2.jpg" alt="materialpro-img" class="rounded-circle" width="40" height="40">
                      <div>
                        <h5 class="fw-semibold mb-0">Virginia Wong</h5>
                        <span class="fs-2 d-flex align-items-center">
                          <i class="ti ti-map-pin text-dark fs-3 me-1"></i>Tunisia
                        </span>
                      </div>
                      <button class="btn btn-outline-primary py-1 px-2 ms-auto">Follow</button>
                    </div>
                  </div>
                </div>
                <div class=" col-md-6 col-xl-4">
                  <div class="card">
                    <div class="card-body p-4 d-flex align-items-center gap-6 flex-wrap">
                      <img src="../../assets/images/profile/user-3.jpg" alt="materialpro-img" class="rounded-circle" width="40" height="40">
                      <div>
                        <h5 class="fw-semibold mb-0">Birdie</h5>
                        <span class="fs-2 d-flex align-items-center">
                          <i class="ti ti-map-pin text-dark fs-3 me-1"></i>Algeria
                        </span>
                      </div>
                      <button class="btn btn-primary py-1 px-2 ms-auto">Followed</button>
                    </div>
                  </div>
                </div>
                <div class=" col-md-6 col-xl-4">
                  <div class="card">
                    <div class="card-body p-4 d-flex align-items-center gap-6 flex-wrap">
                      <img src="../../assets/images/profile/user-4.jpg" alt="materialpro-img" class="rounded-circle" width="40" height="40">
                      <div>
                        <h5 class="fw-semibold mb-0">Steven</h5>
                        <span class="fs-2 d-flex align-items-center">
                          <i class="ti ti-map-pin text-dark fs-3 me-1"></i>Malaysia
                        </span>
                      </div>
                      <button class="btn btn-primary py-1 px-2 ms-auto">Followed</button>
                    </div>
                  </div>
                </div>
                <div class=" col-md-6 col-xl-4">
                  <div class="card">
                    <div class="card-body p-4 d-flex align-items-center gap-6 flex-wrap">
                      <img src="../../assets/images/profile/user-5.jpg" alt="materialpro-img" class="rounded-circle" width="40" height="40">
                      <div>
                        <h5 class="fw-semibold mb-0">Hannah</h5>
                        <span class="fs-2 d-flex align-items-center">
                          <i class="ti ti-map-pin text-dark fs-3 me-1"></i>Grenada
                        </span>
                      </div>
                      <button class="btn btn-primary py-1 px-2 ms-auto">Followed</button>
                    </div>
                  </div>
                </div>
                <div class=" col-md-6 col-xl-4">
                  <div class="card">
                    <div class="card-body p-4 d-flex align-items-center gap-6 flex-wrap">
                      <img src="../../assets/images/profile/user-6.jpg" alt="materialpro-img" class="rounded-circle" width="40" height="40">
                      <div>
                        <h5 class="fw-semibold mb-0">Effie Gross</h5>
                        <span class="fs-2 d-flex align-items-center">
                          <i class="ti ti-map-pin text-dark fs-3 me-1"></i>Azerbaijan
                        </span>
                      </div>
                      <button class="btn btn-outline-primary py-1 px-2 ms-auto">Follow</button>
                    </div>
                  </div>
                </div>
                <div class=" col-md-6 col-xl-4">
                  <div class="card">
                    <div class="card-body p-4 d-flex align-items-center gap-6 flex-wrap">
                      <img src="../../assets/images/profile/user-7.jpg" alt="materialpro-img" class="rounded-circle" width="40" height="40">
                      <div>
                        <h5 class="fw-semibold mb-0">Barton</h5>
                        <span class="fs-2 d-flex align-items-center">
                          <i class="ti ti-map-pin text-dark fs-3 me-1"></i>French Souther
                        </span>
                      </div>
                      <button class="btn btn-outline-primary py-1 px-2 ms-auto">Follow</button>
                    </div>
                  </div>
                </div>
                <div class=" col-md-6 col-xl-4">
                  <div class="card">
                    <div class="card-body p-4 d-flex align-items-center gap-6 flex-wrap">
                      <img src="../../assets/images/profile/user-8.jpg" alt="materialpro-img" class="rounded-circle" width="40" height="40">
                      <div>
                        <h5 class="fw-semibold mb-0">Carolyn</h5>
                        <span class="fs-2 d-flex align-items-center">
                          <i class="ti ti-map-pin text-dark fs-3 me-1"></i>Nauru
                        </span>
                      </div>
                      <button class="btn btn-primary py-1 px-2 ms-auto">Followed</button>
                    </div>
                  </div>
                </div>
                <div class=" col-md-6 col-xl-4">
                  <div class="card">
                    <div class="card-body p-4 d-flex align-items-center gap-6 flex-wrap">
                      <img src="../../assets/images/profile/user-9.jpg" alt="materialpro-img" class="rounded-circle" width="40" height="40">
                      <div>
                        <h5 class="fw-semibold mb-0">Elizabeth</h5>
                        <span class="fs-2 d-flex align-items-center">
                          <i class="ti ti-map-pin text-dark fs-3 me-1"></i>Djibouti
                        </span>
                      </div>
                      <button class="btn btn-primary py-1 px-2 ms-auto">Followed</button>
                    </div>
                  </div>
                </div>
                <div class=" col-md-6 col-xl-4">
                  <div class="card">
                    <div class="card-body p-4 d-flex align-items-center gap-6 flex-wrap">
                      <img src="../../assets/images/profile/user-10.jpg" alt="materialpro-img" class="rounded-circle" width="40" height="40">
                      <div>
                        <h5 class="fw-semibold mb-0">Jon Cohen</h5>
                        <span class="fs-2 d-flex align-items-center">
                          <i class="ti ti-map-pin text-dark fs-3 me-1"></i>United States
                        </span>
                      </div>
                      <button class="btn btn-primary py-1 px-2 ms-auto">Followed</button>
                    </div>
                  </div>
                </div>
                <div class=" col-md-6 col-xl-4">
                  <div class="card">
                    <div class="card-body p-4 d-flex align-items-center gap-6 flex-wrap">
                      <img src="../../assets/images/profile/user-12.jpg" alt="materialpro-img" class="rounded-circle" width="40" height="40">
                      <div>
                        <h5 class="fw-semibold mb-0">Hernandez</h5>
                        <span class="fs-2 d-flex align-items-center">
                          <i class="ti ti-map-pin text-dark fs-3 me-1"></i>Equatorial Guinea
                        </span>
                      </div>
                      <button class="btn btn-outline-primary py-1 px-2 ms-auto">Follow</button>
                    </div>
                  </div>
                </div>
                <div class=" col-md-6 col-xl-4">
                  <div class="card">
                    <div class="card-body p-4 d-flex align-items-center gap-6 flex-wrap">
                      <img src="../../assets/images/profile/user-2.jpg" alt="materialpro-img" class="rounded-circle" width="40" height="40">
                      <div>
                        <h5 class="fw-semibold mb-0">Willie</h5>
                        <span class="fs-2 d-flex align-items-center">
                          <i class="ti ti-map-pin text-dark fs-3 me-1"></i>Solomon Islands
                        </span>
                      </div>
                      <button class="btn btn-primary py-1 px-2 ms-auto">Followed</button>
                    </div>
                  </div>
                </div>
                <div class=" col-md-6 col-xl-4">
                  <div class="card">
                    <div class="card-body p-4 d-flex align-items-center gap-6 flex-wrap">
                      <img src="../../assets/images/profile/user-3.jpg" alt="materialpro-img" class="rounded-circle" width="40" height="40">
                      <div>
                        <h5 class="fw-semibold mb-0">Harvey</h5>
                        <span class="fs-2 d-flex align-items-center">
                          <i class="ti ti-map-pin text-dark fs-3 me-1"></i>Uruguay
                        </span>
                      </div>
                      <button class="btn btn-primary py-1 px-2 ms-auto">Followed</button>
                    </div>
                  </div>
                </div>
                <div class=" col-md-6 col-xl-4">
                  <div class="card">
                    <div class="card-body p-4 d-flex align-items-center gap-6 flex-wrap">
                      <img src="../../assets/images/profile/user-4.jpg" alt="materialpro-img" class="rounded-circle" width="40" height="40">
                      <div>
                        <h5 class="fw-semibold mb-0">Alice George</h5>
                        <span class="fs-2 d-flex align-items-center">
                          <i class="ti ti-map-pin text-dark fs-3 me-1"></i>Madagascar
                        </span>
                      </div>
                      <button class="btn btn-outline-primary py-1 px-2 ms-auto">Follow</button>
                    </div>
                  </div>
                </div>
                <div class=" col-md-6 col-xl-4">
                  <div class="card">
                    <div class="card-body p-4 d-flex align-items-center gap-6 flex-wrap">
                      <img src="../../assets/images/profile/user-12.jpg" alt="materialpro-img" class="rounded-circle" width="40" height="40">
                      <div>
                        <h5 class="fw-semibold mb-0">Simpson</h5>
                        <span class="fs-2 d-flex align-items-center">
                          <i class="ti ti-map-pin text-dark fs-3 me-1"></i>Bahrain
                        </span>
                      </div>
                      <button class="btn btn-primary py-1 px-2 ms-auto">Followed</button>
                    </div>
                  </div>
                </div>
                <div class=" col-md-6 col-xl-4">
                  <div class="card">
                    <div class="card-body p-4 d-flex align-items-center gap-6 flex-wrap">
                      <img src="../../assets/images/profile/user-6.jpg" alt="materialpro-img" class="rounded-circle" width="40" height="40">
                      <div>
                        <h5 class="fw-semibold mb-0">Francis Barber</h5>
                        <span class="fs-2 d-flex align-items-center">
                          <i class="ti ti-map-pin text-dark fs-3 me-1"></i>Colombia
                        </span>
                      </div>
                      <button class="btn btn-outline-primary py-1 px-2 ms-auto">Follow</button>
                    </div>
                  </div>
                </div>
                <div class=" col-md-6 col-xl-4">
                  <div class="card">
                    <div class="card-body p-4 d-flex align-items-center gap-6 flex-wrap">
                      <img src="../../assets/images/profile/user-7.jpg" alt="materialpro-img" class="rounded-circle" width="40" height="40">
                      <div>
                        <h5 class="fw-semibold mb-0">Christian</h5>
                        <span class="fs-2 d-flex align-items-center">
                          <i class="ti ti-map-pin text-dark fs-3 me-1"></i>Maldives
                        </span>
                      </div>
                      <button class="btn btn-primary py-1 px-2 ms-auto">Followed</button>
                    </div>
                  </div>
                </div>
                <div class=" col-md-6 col-xl-4">
                  <div class="card">
                    <div class="card-body p-4 d-flex align-items-center gap-6 flex-wrap">
                      <img src="../../assets/images/profile/user-8.jpg" alt="materialpro-img" class="rounded-circle" width="40" height="40">
                      <div>
                        <h5 class="fw-semibold mb-0">Laura Nelson</h5>
                        <span class="fs-2 d-flex align-items-center">
                          <i class="ti ti-map-pin text-dark fs-3 me-1"></i>St. Helena
                        </span>
                      </div>
                      <button class="btn btn-primary py-1 px-2 ms-auto">Followed</button>
                    </div>
                  </div>
                </div>
                <div class=" col-md-6 col-xl-4">
                  <div class="card">
                    <div class="card-body p-4 d-flex align-items-center gap-6 flex-wrap">
                      <img src="../../assets/images/profile/user-9.jpg" alt="materialpro-img" class="rounded-circle" width="40" height="40">
                      <div>
                        <h5 class="fw-semibold mb-0">Blanche</h5>
                        <span class="fs-2 d-flex align-items-center">
                          <i class="ti ti-map-pin text-dark fs-3 me-1"></i>South Africa
                        </span>
                      </div>
                      <button class="btn btn-primary py-1 px-2 ms-auto">Followed</button>
                    </div>
                  </div>
                </div>
                <div class=" col-md-6 col-xl-4">
                  <div class="card">
                    <div class="card-body p-4 d-flex align-items-center gap-6 flex-wrap">
                      <img src="../../assets/images/profile/user-10.jpg" alt="materialpro-img" class="rounded-circle" width="40" height="40">
                      <div>
                        <h5 class="fw-semibold mb-0">Adam</h5>
                        <span class="fs-2 d-flex align-items-center">
                          <i class="ti ti-map-pin text-dark fs-3 me-1"></i>Suriname
                        </span>
                      </div>
                      <button class="btn btn-primary py-1 px-2 ms-auto">Followed</button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="tab-pane fade" id="pills-friends" role="tabpanel" aria-labelledby="pills-friends-tab" tabindex="0">
              <div class="d-sm-flex align-items-center justify-content-between mt-3 mb-4">
                <h3 class="mb-3 mb-sm-0 fw-semibold d-flex align-items-center">Friends <span class="badge text-bg-secondary fs-2 rounded-4 py-1 px-2 ms-2">20</span>
                </h3>
                <form class="position-relative">
                  <input type="text" class="form-control search-chat py-2 ps-5" id="text-srh" placeholder="Search Friends">
                  <i class="ti ti-search position-absolute top-50 start-0 translate-middle-y text-dark ms-3"></i>
                </form>
              </div>
              <div class="row">
                <div class="col-sm-6 col-lg-4">
                  <div class="card hover-img">
                    <div class="card-body p-4 text-center border-bottom">
                      <img src="../../assets/images/profile/user-3.jpg" alt="materialpro-img" class="rounded-circle mb-3" width="80" height="80">
                      <h5 class="fw-semibold mb-0">Betty Adams</h5>
                      <span class="text-dark fs-2">Medical Secretary</span>
                    </div>
                    <ul class="px-2 py-2 bg-light list-unstyled d-flex align-items-center justify-content-center mb-0">
                      <li class="position-relative">
                        <a class="text-primary d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold" href="javascript:void(0)">
                          <i class="ti ti-brand-facebook"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-danger d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-instagram"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-info d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-github"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-secondary d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-twitter"></i>
                        </a>
                      </li>
                    </ul>
                  </div>
                </div>
                <div class="col-sm-6 col-lg-4">
                  <div class="card hover-img">
                    <div class="card-body p-4 text-center border-bottom">
                      <img src="../../assets/images/profile/user-2.jpg" alt="materialpro-img" class="rounded-circle mb-3" width="80" height="80">
                      <h5 class="fw-semibold mb-0">Inez Lyons</h5>
                      <span class="text-dark fs-2">Medical Technician</span>
                    </div>
                    <ul class="px-2 py-2 bg-light list-unstyled d-flex align-items-center justify-content-center mb-0">
                      <li class="position-relative">
                        <a class="text-primary d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold" href="javascript:void(0)">
                          <i class="ti ti-brand-facebook"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-danger d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-instagram"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-info d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-github"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-secondary d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-twitter"></i>
                        </a>
                      </li>
                    </ul>
                  </div>
                </div>
                <div class="col-sm-6 col-lg-4">
                  <div class="card hover-img">
                    <div class="card-body p-4 text-center border-bottom">
                      <img src="../../assets/images/profile/user-3.jpg" alt="materialpro-img" class="rounded-circle mb-3" width="80" height="80">
                      <h5 class="fw-semibold mb-0">Lydia Bryan</h5>
                      <span class="text-dark fs-2">Preschool Teacher</span>
                    </div>
                    <ul class="px-2 py-2 bg-light list-unstyled d-flex align-items-center justify-content-center mb-0">
                      <li class="position-relative">
                        <a class="text-primary d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold" href="javascript:void(0)">
                          <i class="ti ti-brand-facebook"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-danger d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-instagram"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-info d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-github"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-secondary d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-twitter"></i>
                        </a>
                      </li>
                    </ul>
                  </div>
                </div>
                <div class="col-sm-6 col-lg-4">
                  <div class="card hover-img">
                    <div class="card-body p-4 text-center border-bottom">
                      <img src="../../assets/images/profile/user-4.jpg" alt="materialpro-img" class="rounded-circle mb-3" width="80" height="80">
                      <h5 class="fw-semibold mb-0">Carolyn Bryant</h5>
                      <span class="text-dark fs-2">Legal Secretary</span>
                    </div>
                    <ul class="px-2 py-2 bg-light list-unstyled d-flex align-items-center justify-content-center mb-0">
                      <li class="position-relative">
                        <a class="text-primary d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold" href="javascript:void(0)">
                          <i class="ti ti-brand-facebook"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-danger d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-instagram"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-info d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-github"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-secondary d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-twitter"></i>
                        </a>
                      </li>
                    </ul>
                  </div>
                </div>
                <div class="col-sm-6 col-lg-4">
                  <div class="card hover-img">
                    <div class="card-body p-4 text-center border-bottom">
                      <img src="../../assets/images/profile/user-8.jpg" alt="materialpro-img" class="rounded-circle mb-3" width="80" height="80">
                      <h5 class="fw-semibold mb-0">Paul Benson</h5>
                      <span class="text-dark fs-2">Safety Engineer</span>
                    </div>
                    <ul class="px-2 py-2 bg-light list-unstyled d-flex align-items-center justify-content-center mb-0">
                      <li class="position-relative">
                        <a class="text-primary d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold" href="javascript:void(0)">
                          <i class="ti ti-brand-facebook"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-danger d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-instagram"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-info d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-github"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-secondary d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-twitter"></i>
                        </a>
                      </li>
                    </ul>
                  </div>
                </div>
                <div class="col-sm-6 col-lg-4">
                  <div class="card hover-img">
                    <div class="card-body p-4 text-center border-bottom">
                      <img src="../../assets/images/profile/user-6.jpg" alt="materialpro-img" class="rounded-circle mb-3" width="80" height="80">
                      <h5 class="fw-semibold mb-0">Robert Francis</h5>
                      <span class="text-dark fs-2">Nursing Administrator</span>
                    </div>
                    <ul class="px-2 py-2 bg-light list-unstyled d-flex align-items-center justify-content-center mb-0">
                      <li class="position-relative">
                        <a class="text-primary d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold" href="javascript:void(0)">
                          <i class="ti ti-brand-facebook"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-danger d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-instagram"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-info d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-github"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-secondary d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-twitter"></i>
                        </a>
                      </li>
                    </ul>
                  </div>
                </div>
                <div class="col-sm-6 col-lg-4">
                  <div class="card hover-img">
                    <div class="card-body p-4 text-center border-bottom">
                      <img src="../../assets/images/profile/user-7.jpg" alt="materialpro-img" class="rounded-circle mb-3" width="80" height="80">
                      <h5 class="fw-semibold mb-0">Billy Rogers</h5>
                      <span class="text-dark fs-2">Legal Secretary</span>
                    </div>
                    <ul class="px-2 py-2 bg-light list-unstyled d-flex align-items-center justify-content-center mb-0">
                      <li class="position-relative">
                        <a class="text-primary d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold" href="javascript:void(0)">
                          <i class="ti ti-brand-facebook"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-danger d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-instagram"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-info d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-github"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-secondary d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-twitter"></i>
                        </a>
                      </li>
                    </ul>
                  </div>
                </div>
                <div class="col-sm-6 col-lg-4">
                  <div class="card hover-img">
                    <div class="card-body p-4 text-center border-bottom">
                      <img src="../../assets/images/profile/user-8.jpg" alt="materialpro-img" class="rounded-circle mb-3" width="80" height="80">
                      <h5 class="fw-semibold mb-0">Rosetta Brewer</h5>
                      <span class="text-dark fs-2">Comptroller</span>
                    </div>
                    <ul class="px-2 py-2 bg-light list-unstyled d-flex align-items-center justify-content-center mb-0">
                      <li class="position-relative">
                        <a class="text-primary d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold" href="javascript:void(0)">
                          <i class="ti ti-brand-facebook"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-danger d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-instagram"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-info d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-github"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-secondary d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-twitter"></i>
                        </a>
                      </li>
                    </ul>
                  </div>
                </div>
                <div class="col-sm-6 col-lg-4">
                  <div class="card hover-img">
                    <div class="card-body p-4 text-center border-bottom">
                      <img src="../../assets/images/profile/user-9.jpg" alt="materialpro-img" class="rounded-circle mb-3" width="80" height="80">
                      <h5 class="fw-semibold mb-0">Patrick Knight</h5>
                      <span class="text-dark fs-2">Retail Store Manager</span>
                    </div>
                    <ul class="px-2 py-2 bg-light list-unstyled d-flex align-items-center justify-content-center mb-0">
                      <li class="position-relative">
                        <a class="text-primary d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold" href="javascript:void(0)">
                          <i class="ti ti-brand-facebook"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-danger d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-instagram"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-info d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-github"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-secondary d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-twitter"></i>
                        </a>
                      </li>
                    </ul>
                  </div>
                </div>
                <div class="col-sm-6 col-lg-4">
                  <div class="card hover-img">
                    <div class="card-body p-4 text-center border-bottom">
                      <img src="../../assets/images/profile/user-10.jpg" alt="materialpro-img" class="rounded-circle mb-3" width="80" height="80">
                      <h5 class="fw-semibold mb-0">Francis Sutton</h5>
                      <span class="text-dark fs-2">Astronomer</span>
                    </div>
                    <ul class="px-2 py-2 bg-light list-unstyled d-flex align-items-center justify-content-center mb-0">
                      <li class="position-relative">
                        <a class="text-primary d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold" href="javascript:void(0)">
                          <i class="ti ti-brand-facebook"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-danger d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-instagram"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-info d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-github"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-secondary d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-twitter"></i>
                        </a>
                      </li>
                    </ul>
                  </div>
                </div>
                <div class="col-sm-6 col-lg-4">
                  <div class="card hover-img">
                    <div class="card-body p-4 text-center border-bottom">
                      <img src="../../assets/images/profile/user-11.jpg" alt="materialpro-img" class="rounded-circle mb-3" width="80" height="80">
                      <h5 class="fw-semibold mb-0">Bernice Henry</h5>
                      <span class="text-dark fs-2">Security Consultant</span>
                    </div>
                    <ul class="px-2 py-2 bg-light list-unstyled d-flex align-items-center justify-content-center mb-0">
                      <li class="position-relative">
                        <a class="text-primary d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold" href="javascript:void(0)">
                          <i class="ti ti-brand-facebook"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-danger d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-instagram"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-info d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-github"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-secondary d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-twitter"></i>
                        </a>
                      </li>
                    </ul>
                  </div>
                </div>
                <div class="col-sm-6 col-lg-4">
                  <div class="card hover-img">
                    <div class="card-body p-4 text-center border-bottom">
                      <img src="../../assets/images/profile/user-2.jpg" alt="materialpro-img" class="rounded-circle mb-3" width="80" height="80">
                      <h5 class="fw-semibold mb-0">Estella Garcia</h5>
                      <span class="text-dark fs-2">Lead Software Test Engineer</span>
                    </div>
                    <ul class="px-2 py-2 bg-light list-unstyled d-flex align-items-center justify-content-center mb-0">
                      <li class="position-relative">
                        <a class="text-primary d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold" href="javascript:void(0)">
                          <i class="ti ti-brand-facebook"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-danger d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-instagram"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-info d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-github"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-secondary d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-twitter"></i>
                        </a>
                      </li>
                    </ul>
                  </div>
                </div>
                <div class="col-sm-6 col-lg-4">
                  <div class="card hover-img">
                    <div class="card-body p-4 text-center border-bottom">
                      <img src="../../assets/images/profile/user-3.jpg" alt="materialpro-img" class="rounded-circle mb-3" width="80" height="80">
                      <h5 class="fw-semibold mb-0">Norman Moran</h5>
                      <span class="text-dark fs-2">Engineer Technician</span>
                    </div>
                    <ul class="px-2 py-2 bg-light list-unstyled d-flex align-items-center justify-content-center mb-0">
                      <li class="position-relative">
                        <a class="text-primary d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold" href="javascript:void(0)">
                          <i class="ti ti-brand-facebook"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-danger d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-instagram"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-info d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-github"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-secondary d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-twitter"></i>
                        </a>
                      </li>
                    </ul>
                  </div>
                </div>
                <div class="col-sm-6 col-lg-4">
                  <div class="card hover-img">
                    <div class="card-body p-4 text-center border-bottom">
                      <img src="../../assets/images/profile/user-5.jpg" alt="materialpro-img" class="rounded-circle mb-3" width="80" height="80">
                      <h5 class="fw-semibold mb-0">Jessie Matthews</h5>
                      <span class="text-dark fs-2">Lead Software Engineer</span>
                    </div>
                    <ul class="px-2 py-2 bg-light list-unstyled d-flex align-items-center justify-content-center mb-0">
                      <li class="position-relative">
                        <a class="text-primary d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold" href="javascript:void(0)">
                          <i class="ti ti-brand-facebook"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-danger d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-instagram"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-info d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-github"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-secondary d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-twitter"></i>
                        </a>
                      </li>
                    </ul>
                  </div>
                </div>
                <div class="col-sm-6 col-lg-4">
                  <div class="card hover-img">
                    <div class="card-body p-4 text-center border-bottom">
                      <img src="../../assets/images/profile/user-5.jpg" alt="materialpro-img" class="rounded-circle mb-3" width="80" height="80">
                      <h5 class="fw-semibold mb-0">Elijah Perez</h5>
                      <span class="text-dark fs-2">Special Education Teacher</span>
                    </div>
                    <ul class="px-2 py-2 bg-light list-unstyled d-flex align-items-center justify-content-center mb-0">
                      <li class="position-relative">
                        <a class="text-primary d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold" href="javascript:void(0)">
                          <i class="ti ti-brand-facebook"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-danger d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-instagram"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-info d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-github"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-secondary d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-twitter"></i>
                        </a>
                      </li>
                    </ul>
                  </div>
                </div>
                <div class="col-sm-6 col-lg-4">
                  <div class="card hover-img">
                    <div class="card-body p-4 text-center border-bottom">
                      <img src="../../assets/images/profile/user-6.jpg" alt="materialpro-img" class="rounded-circle mb-3" width="80" height="80">
                      <h5 class="fw-semibold mb-0">Robert Martin</h5>
                      <span class="text-dark fs-2">Transportation Manager</span>
                    </div>
                    <ul class="px-2 py-2 bg-light list-unstyled d-flex align-items-center justify-content-center mb-0">
                      <li class="position-relative">
                        <a class="text-primary d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold" href="javascript:void(0)">
                          <i class="ti ti-brand-facebook"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-danger d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-instagram"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-info d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-github"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-secondary d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-twitter"></i>
                        </a>
                      </li>
                    </ul>
                  </div>
                </div>
                <div class="col-sm-6 col-lg-4">
                  <div class="card hover-img">
                    <div class="card-body p-4 text-center border-bottom">
                      <img src="../../assets/images/profile/user-7.jpg" alt="materialpro-img" class="rounded-circle mb-3" width="80" height="80">
                      <h5 class="fw-semibold mb-0">Elva Wong</h5>
                      <span class="text-dark fs-2">Logistics Manager</span>
                    </div>
                    <ul class="px-2 py-2 bg-light list-unstyled d-flex align-items-center justify-content-center mb-0">
                      <li class="position-relative">
                        <a class="text-primary d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold" href="javascript:void(0)">
                          <i class="ti ti-brand-facebook"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-danger d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-instagram"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-info d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-github"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-secondary d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-twitter"></i>
                        </a>
                      </li>
                    </ul>
                  </div>
                </div>
                <div class="col-sm-6 col-lg-4">
                  <div class="card hover-img">
                    <div class="card-body p-4 text-center border-bottom">
                      <img src="../../assets/images/profile/user-8.jpg" alt="materialpro-img" class="rounded-circle mb-3" width="80" height="80">
                      <h5 class="fw-semibold mb-0">Edith Taylor</h5>
                      <span class="text-dark fs-2">Union Representative</span>
                    </div>
                    <ul class="px-2 py-2 bg-light list-unstyled d-flex align-items-center justify-content-center mb-0">
                      <li class="position-relative">
                        <a class="text-primary d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold" href="javascript:void(0)">
                          <i class="ti ti-brand-facebook"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-danger d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-instagram"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-info d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-github"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-secondary d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-twitter"></i>
                        </a>
                      </li>
                    </ul>
                  </div>
                </div>
                <div class="col-sm-6 col-lg-4">
                  <div class="card hover-img">
                    <div class="card-body p-4 text-center border-bottom">
                      <img src="../../assets/images/profile/user-9.jpg" alt="materialpro-img" class="rounded-circle mb-3" width="80" height="80">
                      <h5 class="fw-semibold mb-0">Violet Jackson</h5>
                      <span class="text-dark fs-2">Agricultural Inspector</span>
                    </div>
                    <ul class="px-2 py-2 bg-light list-unstyled d-flex align-items-center justify-content-center mb-0">
                      <li class="position-relative">
                        <a class="text-primary d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold" href="javascript:void(0)">
                          <i class="ti ti-brand-facebook"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-danger d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-instagram"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-info d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-github"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-secondary d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-twitter"></i>
                        </a>
                      </li>
                    </ul>
                  </div>
                </div>
                <div class="col-sm-6 col-lg-4">
                  <div class="card hover-img">
                    <div class="card-body p-4 text-center border-bottom">
                      <img src="../../assets/images/profile/user-10.jpg" alt="materialpro-img" class="rounded-circle mb-3" width="80" height="80">
                      <h5 class="fw-semibold mb-0">Phoebe Owens</h5>
                      <span class="text-dark fs-2">Safety Engineer</span>
                    </div>
                    <ul class="px-2 py-2 bg-light list-unstyled d-flex align-items-center justify-content-center mb-0">
                      <li class="position-relative">
                        <a class="text-primary d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold" href="javascript:void(0)">
                          <i class="ti ti-brand-facebook"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-danger d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-instagram"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-info d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-github"></i>
                        </a>
                      </li>
                      <li class="position-relative">
                        <a class="text-secondary d-flex align-items-center justify-content-center p-2 fs-5 rounded-circle fw-semibold " href="javascript:void(0)">
                          <i class="ti ti-brand-twitter"></i>
                        </a>
                      </li>
                    </ul>
                  </div>
                </div>
              </div>
            </div>
            <div class="tab-pane fade" id="pills-gallery" role="tabpanel" aria-labelledby="pills-gallery-tab" tabindex="0">
              <div class="d-sm-flex align-items-center justify-content-between mt-3 mb-4">
                <h3 class="mb-3 mb-sm-0 fw-semibold d-flex align-items-center">Gallery <span class="badge text-bg-secondary fs-2 rounded-4 py-1 px-2 ms-2">12</span>
                </h3>
                <form class="position-relative">
                  <input type="text" class="form-control search-chat py-2 ps-5" id="text-srh" placeholder="Search Friends">
                  <i class="ti ti-search position-absolute top-50 start-0 translate-middle-y text-dark ms-3"></i>
                </form>
              </div>
              <div class="row">
                <div class="col-md-6 col-lg-4">
                  <div class="card hover-img overflow-hidden rounded-2">
                    <div class="card-body p-0">
                      <img src="../../assets/images/products/s1.jpg" alt="materialpro-img" height="220" class="w-100 object-fit-cover">
                      <div class="p-4 d-flex align-items-center justify-content-between">
                        <div>
                          <h6 class="mb-0">Isuava wakceajo fe.jpg</h6>
                          <span class="text-dark fs-2">Wed, Dec 14, 2023</span>
                        </div>
                        <div class="dropdown">
                          <a class="text-muted fw-semibold d-flex align-items-center p-1" href="javascript:void(0)" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="ti ti-dots-vertical"></i>
                          </a>
                          <ul class="dropdown-menu overflow-hidden">
                            <li>
                              <a class="dropdown-item" href="javascript:void(0)">Isuava wakceajo fe.jpg</a>
                            </li>
                          </ul>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-md-6 col-lg-4">
                  <div class="card hover-img overflow-hidden rounded-2">
                    <div class="card-body p-0">
                      <img src="../../assets/images/products/s2.jpg" alt="materialpro-img" height="220" class="w-100 object-fit-cover">
                      <div class="p-4 d-flex align-items-center justify-content-between">
                        <div>
                          <h6 class="mb-0">Ip docmowe vemremrif.jpg</h6>
                          <span class="text-dark fs-2">Wed, Dec 14, 2023</span>
                        </div>
                        <div class="dropdown">
                          <a class="text-muted fw-semibold d-flex align-items-center p-1" href="javascript:void(0)" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="ti ti-dots-vertical"></i>
                          </a>
                          <ul class="dropdown-menu overflow-hidden">
                            <li>
                              <a class="dropdown-item" href="javascript:void(0)">Ip docmowe vemremrif.jpg</a>
                            </li>
                          </ul>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-md-6 col-lg-4">
                  <div class="card hover-img overflow-hidden rounded-2">
                    <div class="card-body p-0">
                      <img src="../../assets/images/products/s3.jpg" alt="materialpro-img" height="220" class="w-100 object-fit-cover">
                      <div class="p-4 d-flex align-items-center justify-content-between">
                        <div>
                          <h6 class="mb-0">Duan cosudos utaku.jpg</h6>
                          <span class="text-dark fs-2">Wed, Dec 14, 2023</span>
                        </div>
                        <div class="dropdown">
                          <a class="text-muted fw-semibold d-flex align-items-center p-1" href="javascript:void(0)" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="ti ti-dots-vertical"></i>
                          </a>
                          <ul class="dropdown-menu overflow-hidden">
                            <li>
                              <a class="dropdown-item" href="javascript:void(0)">Duan cosudos utaku.jpg</a>
                            </li>
                          </ul>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-md-6 col-lg-4">
                  <div class="card hover-img overflow-hidden rounded-2">
                    <div class="card-body p-0">
                      <img src="../../assets/images/products/s4.jpg" alt="materialpro-img" height="220" class="w-100 object-fit-cover">
                      <div class="p-4 d-flex align-items-center justify-content-between">
                        <div>
                          <h6 class="mb-0">Fu netbuv oggu.jpg</h6>
                          <span class="text-dark fs-2">Wed, Dec 14, 2023</span>
                        </div>
                        <div class="dropdown">
                          <a class="text-muted fw-semibold d-flex align-items-center p-1" href="javascript:void(0)" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="ti ti-dots-vertical"></i>
                          </a>
                          <ul class="dropdown-menu overflow-hidden">
                            <li>
                              <a class="dropdown-item" href="javascript:void(0)">Fu netbuv oggu.jpg</a>
                            </li>
                          </ul>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-md-6 col-lg-4">
                  <div class="card hover-img overflow-hidden rounded-2">
                    <div class="card-body p-0">
                      <img src="../../assets/images/products/s5.jpg" alt="materialpro-img" height="220" class="w-100 object-fit-cover">
                      <div class="p-4 d-flex align-items-center justify-content-between">
                        <div>
                          <h6 class="mb-0">Di sekog do.jpg</h6>
                          <span class="text-dark fs-2">Wed, Dec 14, 2023</span>
                        </div>
                        <div class="dropdown">
                          <a class="text-muted fw-semibold d-flex align-items-center p-1" href="javascript:void(0)" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="ti ti-dots-vertical"></i>
                          </a>
                          <ul class="dropdown-menu overflow-hidden">
                            <li>
                              <a class="dropdown-item" href="javascript:void(0)">Di sekog do.jpg</a>
                            </li>
                          </ul>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-md-6 col-lg-4">
                  <div class="card hover-img overflow-hidden rounded-2">
                    <div class="card-body p-0">
                      <img src="../../assets/images/products/s6.jpg" alt="materialpro-img" height="220" class="w-100 object-fit-cover">
                      <div class="p-4 d-flex align-items-center justify-content-between">
                        <div>
                          <h6 class="mb-0">Lo jogu camhiisi.jpg</h6>
                          <span class="text-dark fs-2">Thu, Dec 15, 2023</span>
                        </div>
                        <div class="dropdown">
                          <a class="text-muted fw-semibold d-flex align-items-center p-1" href="javascript:void(0)" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="ti ti-dots-vertical"></i>
                          </a>
                          <ul class="dropdown-menu overflow-hidden">
                            <li>
                              <a class="dropdown-item" href="javascript:void(0)">Lo jogu camhiisi.jpg</a>
                            </li>
                          </ul>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-md-6 col-lg-4">
                  <div class="card hover-img overflow-hidden rounded-2">
                    <div class="card-body p-0">
                      <img src="../../assets/images/products/s7.jpg" alt="materialpro-img" height="220" class="w-100 object-fit-cover">
                      <div class="p-4 d-flex align-items-center justify-content-between">
                        <div>
                          <h6 class="mb-0">Orewac huosazud robuf.jpg</h6>
                          <span class="text-dark fs-2">Fri, Dec 16, 2023</span>
                        </div>
                        <div class="dropdown">
                          <a class="text-muted fw-semibold d-flex align-items-center p-1" href="javascript:void(0)" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="ti ti-dots-vertical"></i>
                          </a>
                          <ul class="dropdown-menu overflow-hidden">
                            <li>
                              <a class="dropdown-item" href="javascript:void(0)">Orewac huosazud robuf.jpg</a>
                            </li>
                          </ul>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-md-6 col-lg-4">
                  <div class="card hover-img overflow-hidden rounded-2">
                    <div class="card-body p-0">
                      <img src="../../assets/images/products/s8.jpg" alt="materialpro-img" height="220" class="w-100 object-fit-cover">
                      <div class="p-4 d-flex align-items-center justify-content-between">
                        <div>
                          <h6 class="mb-0">Nira biolaizo tuzi.jpg</h6>
                          <span class="text-dark fs-2">Sat, Dec 17, 2023</span>
                        </div>
                        <div class="dropdown">
                          <a class="text-muted fw-semibold d-flex align-items-center p-1" href="javascript:void(0)" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="ti ti-dots-vertical"></i>
                          </a>
                          <ul class="dropdown-menu overflow-hidden">
                            <li>
                              <a class="dropdown-item" href="javascript:void(0)">Nira biolaizo tuzi.jpg</a>
                            </li>
                          </ul>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-md-6 col-lg-4">
                  <div class="card hover-img overflow-hidden rounded-2">
                    <div class="card-body p-0">
                      <img src="../../assets/images/products/s9.jpg" alt="materialpro-img" height="220" class="w-100 object-fit-cover">
                      <div class="p-4 d-flex align-items-center justify-content-between">
                        <div>
                          <h6 class="mb-0">Peri metu ejvu.jpg</h6>
                          <span class="text-dark fs-2">Sun, Dec 18, 2023</span>
                        </div>
                        <div class="dropdown">
                          <a class="text-muted fw-semibold d-flex align-items-center p-1" href="javascript:void(0)" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="ti ti-dots-vertical"></i>
                          </a>
                          <ul class="dropdown-menu overflow-hidden">
                            <li>
                              <a class="dropdown-item" href="javascript:void(0)">Peri metu ejvu.jpg</a>
                            </li>
                          </ul>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-md-6 col-lg-4">
                  <div class="card hover-img overflow-hidden rounded-2">
                    <div class="card-body p-0">
                      <img src="../../assets/images/products/s10.jpg" alt="materialpro-img" height="220" class="w-100 object-fit-cover">
                      <div class="p-4 d-flex align-items-center justify-content-between">
                        <div>
                          <h6 class="mb-0">Vurnohot tajraje isusufuj.jpg</h6>
                          <span class="text-dark fs-2">Mon, Dec 19, 2023</span>
                        </div>
                        <div class="dropdown">
                          <a class="text-muted fw-semibold d-flex align-items-center p-1" href="javascript:void(0)" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="ti ti-dots-vertical"></i>
                          </a>
                          <ul class="dropdown-menu overflow-hidden">
                            <li>
                              <a class="dropdown-item" href="javascript:void(0)">Vurnohot tajraje isusufuj.jpg</a>
                            </li>
                          </ul>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-md-6 col-lg-4">
                  <div class="card hover-img overflow-hidden rounded-2">
                    <div class="card-body p-0">
                      <img src="../../assets/images/products/s11.jpg" alt="materialpro-img" height="220" class="w-100 object-fit-cover">
                      <div class="p-4 d-flex align-items-center justify-content-between">
                        <div>
                          <h6 class="mb-0">Juc oz ma.jpg</h6>
                          <span class="text-dark fs-2">Tue, Dec 20, 2023</span>
                        </div>
                        <div class="dropdown">
                          <a class="text-muted fw-semibold d-flex align-items-center p-1" href="javascript:void(0)" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="ti ti-dots-vertical"></i>
                          </a>
                          <ul class="dropdown-menu overflow-hidden">
                            <li>
                              <a class="dropdown-item" href="javascript:void(0)">Juc oz ma.jpg</a>
                            </li>
                          </ul>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-md-6 col-lg-4">
                  <div class="card hover-img overflow-hidden rounded-2">
                    <div class="card-body p-0">
                      <img src="../../assets/images/products/s12.jpg" alt="materialpro-img" height="220" class="w-100 object-fit-cover">
                      <div class="p-4 d-flex align-items-center justify-content-between">
                        <div>
                          <h6 class="mb-0">Povipvez marjelliz zuuva.jpg</h6>
                          <span class="text-dark fs-2">Wed, Dec 21, 2023</span>
                        </div>
                        <div class="dropdown">
                          <a class="text-muted fw-semibold d-flex align-items-center p-1" href="javascript:void(0)" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="ti ti-dots-vertical"></i>
                          </a>
                          <ul class="dropdown-menu overflow-hidden">
                            <li>
                              <a class="dropdown-item" href="javascript:void(0)">Povipvez marjelliz zuuva.jpg</a>
                            </li>
                          </ul>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      <script>
  function handleColorTheme(e) {
    $("html").attr("data-color-theme", e);
    $(e).prop("checked", !0);
  }
</script>
      <button class="btn btn-primary p-3 rounded-circle d-flex align-items-center justify-content-center customizer-btn" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasExample" aria-controls="offcanvasExample">
        <i class="icon ti ti-settings fs-7 text-white"></i>
      </button>

      <div class="offcanvas customizer offcanvas-end" tabindex="-1" id="offcanvasExample" aria-labelledby="offcanvasExampleLabel">
        <div class="d-flex align-items-center justify-content-between p-3 border-bottom">
          <h4 class="offcanvas-title fw-semibold" id="offcanvasExampleLabel">
            Settings
          </h4>
          <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body h-n80" data-simplebar>
          <h6 class="fw-semibold fs-4 mb-2">Theme</h6>

          <div class="d-flex flex-row gap-3 customizer-box" role="group">
            <input type="radio" class="btn-check light-layout " name="theme-layout" id="light-layout" autocomplete="off" />
            <label class="btn p-9 btn-outline-primary rounded" for="light-layout"> <iconify-icon icon="solar:sun-2-outline" class="icon fs-7 me-2"></iconify-icon>Light</label>
            <input type="radio" class="btn-check dark-layout" name="theme-layout" id="dark-layout" autocomplete="off" />
            <label class="btn p-9 btn-outline-primary rounded" for="dark-layout"><iconify-icon icon="solar:moon-outline" class="icon fs-7 me-2"></iconify-icon>Dark</label>
          </div>

          <h6 class="mt-5 fw-semibold fs-4 mb-2">Theme Direction</h6>
          <div class="d-flex flex-row gap-3 customizer-box" role="group">
            <input type="radio" class="btn-check" name="direction-l" id="ltr-layout" autocomplete="off" />
            <label class="btn p-9 btn-outline-primary rounded" for="ltr-layout"><iconify-icon icon="solar:align-left-linear" class="icon fs-7 me-2"></iconify-icon>LTR</label>

            <input type="radio" class="btn-check" name="direction-l" id="rtl-layout" autocomplete="off" />
            <label class="btn p-9 btn-outline-primary rounded" for="rtl-layout">
              <iconify-icon icon="solar:align-right-linear" class="icon fs-7 me-2"></iconify-icon>RTL
            </label>
          </div>

          <h6 class="mt-5 fw-semibold fs-4 mb-2">Theme Colors</h6>

          <div class="d-flex flex-row flex-wrap gap-3 customizer-box color-pallete" role="group">
            <input type="radio" class="btn-check" name="color-theme-layout" id="Blue_Theme" autocomplete="off" />
            <label class="btn p-9 btn-outline-primary d-flex align-items-center justify-content-center rounded" onclick="handleColorTheme('Blue_Theme')" for="Blue_Theme" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="BLUE_THEME">
              <div class="color-box rounded-circle d-flex align-items-center justify-content-center skin-1">
                <i class="ti ti-check text-white d-flex icon fs-5"></i>
              </div>
            </label>

            <input type="radio" class="btn-check" name="color-theme-layout" id="Aqua_Theme" autocomplete="off" />
            <label class="btn p-9 btn-outline-primary d-flex align-items-center justify-content-center rounded" onclick="handleColorTheme('Aqua_Theme')" for="Aqua_Theme" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="AQUA_THEME">
              <div class="color-box rounded-circle d-flex align-items-center justify-content-center skin-2">
                <i class="ti ti-check text-white d-flex icon fs-5"></i>
              </div>
            </label>

            <input type="radio" class="btn-check" name="color-theme-layout" id="Purple_Theme" autocomplete="off" />
            <label class="btn p-9 btn-outline-primary d-flex align-items-center justify-content-center rounded" onclick="handleColorTheme('Purple_Theme')" for="Purple_Theme" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="PURPLE_THEME">
              <div class="color-box rounded-circle d-flex align-items-center justify-content-center skin-3">
                <i class="ti ti-check text-white d-flex icon fs-5"></i>
              </div>
            </label>

            <input type="radio" class="btn-check" name="color-theme-layout" id="green-theme-layout" autocomplete="off" />
            <label class="btn p-9 btn-outline-primary d-flex align-items-center justify-content-center rounded" onclick="handleColorTheme('Green_Theme')" for="green-theme-layout" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="GREEN_THEME">
              <div class="color-box rounded-circle d-flex align-items-center justify-content-center skin-4">
                <i class="ti ti-check text-white d-flex icon fs-5"></i>
              </div>
            </label>

            <input type="radio" class="btn-check" name="color-theme-layout" id="cyan-theme-layout" autocomplete="off" />
            <label class="btn p-9 btn-outline-primary d-flex align-items-center justify-content-center rounded" onclick="handleColorTheme('Cyan_Theme')" for="cyan-theme-layout" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="CYAN_THEME">
              <div class="color-box rounded-circle d-flex align-items-center justify-content-center skin-5">
                <i class="ti ti-check text-white d-flex icon fs-5"></i>
              </div>
            </label>

            <input type="radio" class="btn-check" name="color-theme-layout" id="orange-theme-layout" autocomplete="off" />
            <label class="btn p-9 btn-outline-primary d-flex align-items-center justify-content-center rounded" onclick="handleColorTheme('Orange_Theme')" for="orange-theme-layout" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="ORANGE_THEME">
              <div class="color-box rounded-circle d-flex align-items-center justify-content-center skin-6">
                <i class="ti ti-check text-white d-flex icon fs-5"></i>
              </div>
            </label>
          </div>

          <h6 class="mt-5 fw-semibold fs-4 mb-2">Layout Type</h6>
          <div class="d-flex flex-row gap-3 customizer-box" role="group">
            <div>
              <input type="radio" class="btn-check" name="page-layout" id="vertical-layout" autocomplete="off" />
              <label class="btn p-9 btn-outline-primary rounded" for="vertical-layout">
                <iconify-icon icon="solar:slider-vertical-minimalistic-linear" class="icon fs-7 me-2"></iconify-icon>Vertical
              </label>
            </div>
            <div>
              <input type="radio" class="btn-check" name="page-layout" id="horizontal-layout" autocomplete="off" />
              <label class="btn p-9 btn-outline-primary rounded" for="horizontal-layout">
                <iconify-icon icon="solar:slider-minimalistic-horizontal-outline" class="icon fs-7 me-2"></iconify-icon>
                Horizontal
              </label>
            </div>
          </div>

          <h6 class="mt-5 fw-semibold fs-4 mb-2">Container Option</h6>

          <div class="d-flex flex-row gap-3 customizer-box" role="group">
            <input type="radio" class="btn-check" name="layout" id="boxed-layout" autocomplete="off" />
            <label class="btn p-9 btn-outline-primary rounded" for="boxed-layout">
              <iconify-icon icon="solar:cardholder-linear" class="icon fs-7 me-2"></iconify-icon>
              Boxed
            </label>

            <input type="radio" class="btn-check" name="layout" id="full-layout" autocomplete="off" />
            <label class="btn p-9 btn-outline-primary rounded" for="full-layout">
              <iconify-icon icon="solar:scanner-linear" class="icon fs-7 me-2"></iconify-icon> Full
            </label>
          </div>

          <h6 class="fw-semibold fs-4 mb-2 mt-5">Sidebar Type</h6>
          <div class="d-flex flex-row gap-3 customizer-box" role="group">
            <a href="javascript:void(0)" class="fullsidebar">
              <input type="radio" class="btn-check" name="sidebar-type" id="full-sidebar" autocomplete="off" />
              <label class="btn p-9 btn-outline-primary rounded" for="full-sidebar"><iconify-icon icon="solar:sidebar-minimalistic-outline" class="icon fs-7 me-2"></iconify-icon> Full</label>
            </a>
            <div>
              <input type="radio" class="btn-check " name="sidebar-type" id="mini-sidebar" autocomplete="off" />
              <label class="btn p-9 btn-outline-primary rounded" for="mini-sidebar">
                <iconify-icon icon="solar:siderbar-outline" class="icon fs-7 me-2"></iconify-icon>Collapse
              </label>
            </div>
          </div>

          <h6 class="mt-5 fw-semibold fs-4 mb-2">Card With</h6>

          <div class="d-flex flex-row gap-3 customizer-box" role="group">
            <input type="radio" class="btn-check" name="card-layout" id="card-with-border" autocomplete="off" />
            <label class="btn p-9 btn-outline-primary rounded" for="card-with-border"><iconify-icon icon="solar:library-broken" class="icon fs-7 me-2"></iconify-icon>Border</label>

            <input type="radio" class="btn-check" name="card-layout" id="card-without-border" autocomplete="off" />
            <label class="btn p-9 btn-outline-primary rounded" for="card-without-border">
              <iconify-icon icon="solar:box-outline " class="icon fs-7 me-2"></iconify-icon>Shadow
            </label>
          </div>
        </div>
      </div>

      <script>
  function handleColorTheme(e) {
    document.documentElement.setAttribute("data-color-theme", e);
  }
</script>
    </div>

    <!--  Search Bar -->
    <div class="modal fade" id="exampleModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content rounded-1">
          <div class="modal-header border-bottom">
            <input type="search" class="form-control fs-2" placeholder="Search here" id="search" />
            <a href="javascript:void(0)" data-bs-dismiss="modal" class="lh-1">
              <i class="ti ti-x fs-5 ms-3"></i>
            </a>
          </div>
          <div class="modal-body message-body" data-simplebar="">
            <h5 class="mb-0 fs-5 p-1">Quick Page Links</h5>
            <ul class="list mb-0 py-2">
              <li class="p-1 mb-1 px-2 rounded bg-hover-light-black">
                <a href="javascript:void(0)">
                  <span class="h6 mb-1">Modern</span>
                  <span class="fs-2 text-muted d-block">/dashboards/dashboard1</span>
                </a>
              </li>
              <li class="p-1 mb-1 px-2 rounded bg-hover-light-black">
                <a href="javascript:void(0)">
                  <span class="h6 mb-1">Dashboard</span>
                  <span class="fs-2 text-muted d-block">/dashboards/dashboard2</span>
                </a>
              </li>
              <li class="p-1 mb-1 px-2 rounded bg-hover-light-black">
                <a href="javascript:void(0)">
                  <span class="h6 mb-1">Contacts</span>
                  <span class="fs-2 text-muted d-block">/apps/contacts</span>
                </a>
              </li>
              <li class="p-1 mb-1 px-2 rounded bg-hover-light-black">
                <a href="javascript:void(0)">
                  <span class="h6 mb-1">Posts</span>
                  <span class="fs-2 text-muted d-block">/apps/blog/posts</span>
                </a>
              </li>
              <li class="p-1 mb-1 px-2 rounded bg-hover-light-black">
                <a href="javascript:void(0)">
                  <span class="h6 mb-1">Detail</span>
                  <span class="fs-2 text-muted d-block">/apps/blog/detail/streaming-video-way-before-it-was-cool-go-dark-tomorrow</span>
                </a>
              </li>
              <li class="p-1 mb-1 px-2 rounded bg-hover-light-black">
                <a href="javascript:void(0)">
                  <span class="h6 mb-1">Shop</span>
                  <span class="fs-2 text-muted d-block">/apps/ecommerce/shop</span>
                </a>
              </li>
              <li class="p-1 mb-1 px-2 rounded bg-hover-light-black">
                <a href="javascript:void(0)">
                  <span class="h6 mb-1">Modern</span>
                  <span class="fs-2 text-muted d-block">/dashboards/dashboard1</span>
                </a>
              </li>
              <li class="p-1 mb-1 px-2 rounded bg-hover-light-black">
                <a href="javascript:void(0)">
                  <span class="h6 mb-1">Dashboard</span>
                  <span class="fs-2 text-muted d-block">/dashboards/dashboard2</span>
                </a>
              </li>
              <li class="p-1 mb-1 px-2 rounded bg-hover-light-black">
                <a href="javascript:void(0)">
                  <span class="h6 mb-1">Contacts</span>
                  <span class="fs-2 text-muted d-block">/apps/contacts</span>
                </a>
              </li>
              <li class="p-1 mb-1 px-2 rounded bg-hover-light-black">
                <a href="javascript:void(0)">
                  <span class="h6 mb-1">Posts</span>
                  <span class="fs-2 text-muted d-block">/apps/blog/posts</span>
                </a>
              </li>
              <li class="p-1 mb-1 px-2 rounded bg-hover-light-black">
                <a href="javascript:void(0)">
                  <span class="h6 mb-1">Detail</span>
                  <span class="fs-2 text-muted d-block">/apps/blog/detail/streaming-video-way-before-it-was-cool-go-dark-tomorrow</span>
                </a>
              </li>
              <li class="p-1 mb-1 px-2 rounded bg-hover-light-black">
                <a href="javascript:void(0)">
                  <span class="h6 mb-1">Shop</span>
                  <span class="fs-2 text-muted d-block">/apps/ecommerce/shop</span>
                </a>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>


  </div>
  <div class="dark-transparent sidebartoggler"></div>
  <!-- Import Js Files -->
  <script src="../../assets/js/breadcrumb/breadcrumbChart.js"></script>
  <script src="../../assets/libs/apexcharts/dist/apexcharts.min.js"></script>
  <script src="../../assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../../assets/libs/simplebar/dist/simplebar.min.js"></script>
  <script src="../../assets/js/theme/app.dark.init.js"></script>
  <script src="../../assets/js/theme/theme.js"></script>
  <script src="../../assets/js/theme/app.min.js"></script>
  <script src="../../assets/js/theme/sidebarmenu.js"></script>
  <script src="../../assets/js/theme/feather.min.js"></script>

  <!-- solar icons -->
  <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>
</body>

</html>