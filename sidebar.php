<div class="sidebar">
        <a href="dashboard1.php" class="logo">
            <i class="fa fa-microphone"></i>
            <div class="logo-name"><span>Vo</span>CE</div>
        </a>
        <ul class="side-menu">
            <li class="<?php echo ($current_page === 'dashboard1.php') ? 'active' : ''; ?>"><a href="dashboard1.php"><i class='bx bxs-dashboard'></i>Dashboard</a></li> 
            <li class="<?php echo ($current_page === 'text-text.php') ? 'active' : ''; ?>"><a href="text-text.php"><img src="images/sidebartext.png" alt="scroll icon" width="25" height="25" style="margin-left: 5px;">
            &nbsp Text-Text</a></li>        
            <li class="<?php echo ($current_page === 'history_audio.php') ? 'active' : ''; ?>">
            <a href="history_audio.php"><img src="images/sidebaraudio.png" alt="scroll icon" width="25" height="25" style="margin-left: 5px;">
            &nbsp Audio to Text</a></li>
            

            <?php if($_SESSION['type'] == 'admin') : ?>
            <li class="<?php echo ($current_page === 'admin.php') ? 'active' : ''; ?>">
                <a href="admin.php"><img src="images/sidebartext.png" alt="scroll icon" width="25" height="25" style="margin-left: 5px;">
            &nbsp Admin</a></li>     
            <li class="<?php echo ($current_page === 'user-table.php') ? 'active' : ''; ?>">
                    <a href="user-table.php"><img src="images/sidebaraudio.png" alt="scroll icon" width="25" height="25" style="margin-left: 5px;">
                &nbsp Users</a></li>   
            <?php endif ?>
            <li><a href="#"><i class='bx bx-cog'></i>Settings</a></li>



        </ul>
        <ul class="side-menu">
            <li>
                <a href="utilities/logout.php" class="logout">
                    <i class='bx bx-log-out-circle'></i>
                    Logout
                </a>
            </li>
        </ul>
    </div>