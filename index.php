<?php
require('header.html');
require('authorize.php');
?>

<script>
<?php
// Gather Basecamp Data ====================================================================================
// Get All Users
$users = array();
for ($x = 1; $x <= 5; $x++) {
  $client->CallAPI(
    "{$url}/people.json?page=$x",
    'GET', array(), array('FailOnAccessError'=>true), $people);
  foreach($people as $user) {
    $email = explode("@", $user->email_address);
    $email = strtolower($email[1]);
    if ($email == "lodgedesign.com" || $email == "oneluckyguitar.com" || $email == "lodgemail.com") {
      $users[$user->name] = $user;
    }
  }
}
ksort($users);
$all_users = json_encode($users);
echo "var all_users = " . $all_users . ";";

// Get User Profile
$client->CallAPI(
  "{$url}/my/profile.json",
  'GET', array(), array('FailOnAccessError'=>true), $responseID);
$profile = json_encode($responseID);
echo "var profile = " . $profile . ";";

// Get All Active Projects
$active_projects = array();
for ($x = 1; $x <= 5; $x++) {
  $client->CallAPI(
    "{$url}/projects.json?page=$x",
    'GET', array(), array('FailOnAccessError'=>true), $active_project);
  foreach($active_project as $active_project) {
    $active_projects[$active_project->name] = $active_project;
  }
}
ksort($active_projects);
$active_projects = json_encode($active_projects);
echo "var active_projects = " . $active_projects . ";";

// Get All Archived Projects
$archived_projects = array();
for ($x = 1; $x <= 5; $x++) {
  $client->CallAPI(
    "{$url}/projects.json?status=archived&page=$x",
    'GET', array(), array('FailOnAccessError'=>true), $archived_project);
  foreach($archived_project as $archived_project) {
    $archived_projects[$archived_project->name] = $archived_project;
  }
}
ksort($archived_projects);
$archived_projects = json_encode($archived_projects);
echo "var archived_projects = " . $archived_projects . ";";
?>
</script>
<body>
  <header></header>
  <nav>
    <div class="nav-wrapper max-width">
      <div class="user_info">
        <div class="user_image"></div>
        <div>
          <div class="user_name"></div>
          <div class="user_id"></div>
        </div>
      </div>
      <div class="brand-logo"><a href="index.php"><img src="images/brand.png" alt="LODGE" style="width:275px;"/></a></div>
      <ul>
        <li><a href="#report_modal" class="modal-trigger">Create Report</a></li>
        <li class="signout"><a href="#!">Sign Out</a></li>
      </ul>
    </div>
  </nav>

<!-- UI Elements ======================================================================================= -->
  <main>
    <div class="row">
      <!-- Add Log -->
      <div class="add_log_container col s12">
        <div class="max-width">
          <h4>
            Add Log(s)
            <button class="btn waves-effect right" id="submit"><i class="fas fa-check"></i> Submit</button>
            <button class="btn waves-effect right add_row"><i class="fas fa-plus"></i> Add Row</button>
          </h4>
          <div class="add_logs">
            <div class="add_log col s12">
              <div class="col s12 m1 center">
                <button class="btn-flat waves-effect remove_row col s12">
                  Remove
                </button>
              </div>
              <div class="input-field col s12 m2">
                <input type="text" class="datepicker" placeholder="Date*">
              </div>
              <div class="input-field col s12 m3">
                <select class="project browser-default">
                  <option value="" disabled selected>Project Name*</option>
                </select>
              </div>
              <div class="input-field col s12 m2">
                <select class="job_code browser-default">
                  <option value="" disabled selected>Job Code</option>
                </select>
              </div>
              <div class="input-field col s12 m3">
                <input class="description" type="text" maxlength="75" placeholder="Description">
              </div>
              <div class="input-field col s12 m1">
                <input class="hours" type="number" max="99" min="0" placeholder="Hours*">
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="row flex-container max-width">
      <!-- Project List -->
      <div class="projects">
        <div class="max-width">
          <h4>
            View Projects
            <button class="btn waves-effect right toggle toggle_active active">Active</button>
            <button class="btn waves-effect right toggle toggle_archived">Archived</button>
          </h4>
          <div class="projects_archived col s12">
            <div class="input-field col s12">
              <input id="search_archived" class="search" type="text">
              <label for="search_archived">Search Achived Jobs</label>
            </div>
          </div>
          <div class="projects_active col s12">
            <div class="input-field col s12">
              <input id="search_active" class="search" type="text">
              <label for="search_active">Search Active Jobs</label>
            </div>
          </div>
        </div>
      </div>
      <!-- Logs List -->
      <div class="logs">
        <h4 class="project_name center">View Logs</h4>
        <div class="project_logs">
          <table class="striped">
            <thead>
              <tr>
                <th><a download="logs.csv" class="btn-flat waves-effect csv"><i class="far fa-arrow-alt-circle-down"></i> CSV</a></th>
                <th>Date</th>
                <th>User Name</th>
                <th>Job Code</th>
                <th>Description</th>
                <th style="text-align:right">Hours: <span class="total_hours"></span></th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>

    </div>

    <!-- Edit Modal -->
    <div id="edit_modal" class="modal row">
      <div class="modal-content">
        <h4 class="project_name"></h4>
        <input type="hidden" id="edit_log_id" />
        <input type="hidden" id="edit_log_rev" />
        <div class="row">
          <div class="input-field col s6">
            <input disabled id="edit_user_name" type="text">
            <label for="edit_user_name">User Name</label>
          </div>
          <div class="input-field col s6">
            <input disabled id="edit_user_id" type="text">
            <label for="edit_user_id">User Id</label>
          </div>
        </div>
        <div class="row">
          <div class="input-field col s12">
            <input type="text" id="edit_date" class="datepicker">
            <label for="edit_date">Date*</label>
          </div>
        </div>
        <div class="row">
          <div class="input-field col s12">
            <select id="edit_project" class="browser-default">
              <option value="" disabled selected>Project Name*</option>
            </select>
          </div>
        </div>
        <div class="row">
          <div class="input-field col s12">
            <select id="edit_job_code" class="browser-default">
              <option value="" disabled selected>Job Code</option>
            </select>
          </div>
        </div>
        <div class="row">
          <div class="input-field col s12">
            <input id="edit_description" type="text" maxlength="75">
            <label for="edit_description">Description</label>
          </div>
        </div>
        <div class="row">
          <div class="input-field col s12">
            <input id="edit_hours" type="number" max="99" min="0">
            <label for="edit_hours">Hours*</label>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <a href="#!" class="modal-action modal-close waves-effect btn-flat submit"><i class="fas fa-check"></i> Submit</a>
      </div>
    </div>

    <!-- Report Modal -->
    <div id="report_modal" class="modal row">
      <div class="modal-content">
        <h4>Create a Report</h4>
        <div class="row">
          <div class="input-field col s12 m6">
            <select multiple id="report_user" class="browser-default">
              <option value="" disabled selected>Choose User(s)</option>
            </select>
          </div>
          <div class="input-field col s12 m6">
            <select multiple id="report_project" class="browser-default">
              <option value="" disabled selected>Choose Project(s)</option>
              <option value="all">All Projects</option>
            </select>
          </div>
        </div>
        <div class="row">
          <div class="input-field col s12 m6">
            <i class="far fa-calendar-alt prefix"></i>
            <input type="text" id="report_start_date" class="datepicker">
            <label for="report_start_date">Start Date</label>
          </div>
          <div class="input-field col s12 m6">
            <i class="far fa-calendar-alt prefix"></i>
            <input type="text" id="report_end_date" class="datepicker">
            <label for="report_end_date">End Date</label>
          </div>
        </div>

      </div>
      <div class="modal-footer">
        <a href="#!" class="modal-action modal-close waves-effect btn-flat submit"><i class="fas fa-check"></i> Run It!</a>
      </div>
    </div>

  </main>
<?php require('footer.html'); ?>
</body>
</html>