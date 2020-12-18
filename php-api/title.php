<div id="flex">
    <div id="title-lhs">
        <?php echo "<img src='/assets/img/$id.jpg' onerror=\"this.onerror=null;this.src='/assets/img/noposter.jpg';\" />"; ?>
    </div>
    <div id="title-rhs">
        <div>
            <h2><?php echo "$name ($year)"; ?></h2>
        </div>
        <div>
            <h3>Genres</h3>
            <?php echo "$genres"; ?>
        </div>
        <div>
            <h3>Rating</h3>
            <?php
                $total = 10;
                foreach(range(1, $rating) as $i){
                    echo "<span class='fa fa-star checked'></span>";
                    $total--;
                }
                foreach(range(0, $total - 1) as $i){
                    echo "<span class='fa fa-star'></span>";
                }
                echo " (" . number_format($votes) . ")";
            ?>
        </div>
        <br/><br/>
        <div>
            <?php echo "<a target='_blank' href='https://www.imdb.com/title/$id/'><i class='fa fa-imdb imdb'></i></a>"; ?>
        </div>
    </div>
</div>