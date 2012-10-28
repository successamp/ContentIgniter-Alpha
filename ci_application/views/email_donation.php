<html>
<table style="font-size:90%;text-align:left;width:95%;">
    <tr>
        <td colspan="2"><h3><?=$name;?></h3></td>
    </tr>
    <tr>
        <td colspan="2"><h4>Has posted an inquiry to <?=$subdomain;?></h4></td>
    </tr>
    <?php
    if (!empty($ppc)) {
        echo '<tr><td colspan="2"><strong>PPC: ' . $ppc . '</strong></td></tr>';
        if ($changed_chapter) {
            echo'<tr><td colspan="2"><strong>PPC: was from a different chapter ( ' . $entrance_domain . ' )</strong></td></tr>';
        }
    }
    ?>
    <tr>
        <td colspan="2"><?=date('l jS \of F Y h:i:s A');?>
            <hr/>
        </td>
    </tr>
    <?php
    if (isset($_POST['mail_check']) && $_POST['mail_check'] == 'pickup') {
        ?>
        <tr>
            <td><strong>Name:</strong></td>
            <td><?=$name;?></td>
        </tr>
        <tr>
            <td><strong>E-Mail:</strong></td>
            <td><?=$email;?></td>
        </tr>
        <tr>
            <td><strong>Address:</strong></td>
            <td><?=$address;?></td>
        </tr>
        <tr>
            <td><strong>Address 2:</strong></td>
            <td><?=$address2;?></td>
        </tr>
        <tr>
            <td><strong>City:</strong></td>
            <td><?=$city;?></td>
        </tr>
        <tr>
            <td><strong>State:</strong></td>
            <td><?=$state;?></td>
        </tr>
        <tr>
            <td><strong>Zip:</strong></td>
            <td><?=$zip;?></td>
        </tr>
        <tr>
            <td><strong>Mail Receipt To:</strong></td>
            <td>Pickup Address</td>
        </tr>
        <tr>
            <td colspan="2">
                <hr/>
            </td>
        </tr>
        <?php
    } else {
        ?>
        <tr>
            <td><strong>Name:</strong></td>
            <td><?=$name;?></td>
        </tr>
        <tr>
            <td><strong>E-Mail:</strong></td>
            <td><?=$email;?></td>
        </tr>
        <tr>
            <td style="border-right:1px solid black;"><strong>Pickup Address</strong></td>
            <td><strong>Mailing Address</strong></td>
        </tr>
        <tr>
            <td style="border-right:1px solid black;"><?=$address;?></td>
            <td><?=$address_mail;?></td>
        </tr>
        <tr>
            <td style="border-right:1px solid black;"><strong>Pickup Address 2:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</strong>
            </td>
            <td><strong>Mailing Address 2:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</strong>
            </td>
        </tr>
        <tr>
            <td style="border-right:1px solid black;"><?=$address2;?></td>
            <td><?=$address_mail2;?></td>
        </tr>
        <tr>
            <td style="border-right:1px solid black;"><strong>Pickup City:</strong></td>
            <td><strong>Mailing City:</strong></td>
        </tr>
        <tr>
            <td style="border-right:1px solid black;"><?=$city;?></td>
            <td><?=$city_mail;?></td>
        </tr>
        <tr>
            <td style="border-right:1px solid black;"><strong>Pickup State:</strong></td>
            <td><strong>Mailing State:</strong></td>
        </tr>
        <tr>
            <td style="border-right:1px solid black;"><?=$state;?></td>
            <td><?=$state_mail;?></td>
        </tr>
        <tr>
            <td style="border-right:1px solid black;"><strong>Pickup Zip:</strong></td>
            <td><strong>Mailing Zip:</strong></td>
        </tr>
        <tr>
            <td style="border-right:1px solid black;"><?=$zip;?></td>
            <td><?=$zip_mail;?></td>
        </tr>
        <tr>
            <td colspan="2">
                <hr/>
            </td>
        </tr>
        <?php
    }
    ?>
    <tr>
        <td><strong>Phone:</strong></td>
        <td><?=$phone;?></td>
    </tr>
    <tr>
        <td><strong>Make:</strong></td>
        <td><?=$make;?></td>
    </tr>
    <tr>
        <td><strong>Model:</strong></td>
        <td><?=$model;?></td>
    </tr>
    <tr>
        <td><strong>Year:</strong></td>
        <td><?=$year;?></td>
    </tr>
    <tr>
        <td><strong>Miles:</strong></td>
        <td><?=$miles;?></td>
    </tr>
    <tr>
        <td><strong>Color:</strong></td>
        <td><?=$color;?></td>
    </tr>
    <tr>
        <td><strong>Mechanical:</strong></td>
        <td><?=$mechanical;?></td>
    </tr>
    <tr>
        <td><strong>Running:</strong></td>
        <td><?=empty($running) ? 'No' : 'Yes';?></td>
    </tr>
    <tr>
        <td><strong>Title:</strong></td>
        <td><?=$title;?></td>
    </tr>
    <tr>
        <td><strong>Heard Ad On Radio:</strong></td>
        <td><?=$heard_radio;?></td>
    </tr>
    <tr>
        <td><strong>Heard About Us:</strong></td>
        <td><?=$heard_about_us;?></td>
    </tr>
    <tr>
        <td><strong>Best Phone To Call:</strong></td>
        <td><?=$best_phone;?></td>
    </tr>
    <tr>
        <td><strong>Best Time To Call:</strong></td>
        <td><?=$best_time;?></td>
    </tr>
    <tr>
        <td colspan="2">
            <hr/>
        </td>
    </tr>
    <tr>
        <td><strong>Referrer:</strong></td>
        <td><?=$refer;?></td>
    </tr>
    <tr>
        <td><strong>Entry URL:</strong></td>
        <td><?=$entrance_domain . $entrance_page;?></td>
    </tr>
    <tr>
        <td><strong>Donor Reference Code:</strong></td>
        <td><?=$donor_reference;?></td>
    </tr>
    <?php
    if (!empty($notes)) {
        echo '<tr><td><strong>Notes:</strong></td><td>' . $notes . '</td></tr>';
    }
    ?>
</table>
</html>