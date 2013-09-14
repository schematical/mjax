
    <ul>
        <?php foreach($_CONTROL->ChildControls as $intIndex => $lnkPage){ ?>
            <li><?php $lnkPage->Render(); ?></li>
        <?php } ?>
    </ul>
