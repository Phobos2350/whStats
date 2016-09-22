<?php

/* entities.html */
class __TwigTemplate_75d98b0aaa548ffd599be3215cff9dcceb5c911c8e61e8593b0f88ae543bbf8c extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        // line 1
        $this->parent = $this->loadTemplate("header.html", "entities.html", 1);
        $this->blocks = array(
            'title' => array($this, 'block_title'),
            'version' => array($this, 'block_version'),
            'content' => array($this, 'block_content'),
        );
    }

    protected function doGetParent(array $context)
    {
        return "header.html";
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        $this->parent->display($context, array_merge($this->blocks, $blocks));
    }

    // line 2
    public function block_title($context, array $blocks = array())
    {
        echo "WH Stats 2.0";
    }

    // line 3
    public function block_version($context, array $blocks = array())
    {
        echo "2.0";
    }

    // line 4
    public function block_content($context, array $blocks = array())
    {
        // line 5
        echo "
<!-- Modal Structure -->
<div id=\"modal1\" class=\"modal\">
  <div class=\"modal-content\">
    <h4 class=\"modal-content-text\">Loading Stats</h4>
    <i>This May Take A Few Seconds! Please Be Patient!</i><br>
    <div class=\"preloader-wrapper big active\">
      <div class=\"spinner-layer spinner-blue-only\">
        <div class=\"circle-clipper left\">
          <div class=\"circle\"></div>
        </div><div class=\"gap-patch\">
          <div class=\"circle\"></div>
        </div><div class=\"circle-clipper right\">
          <div class=\"circle\"></div>
        </div>
      </div>
    </div>
  </div>
</div>
<!--Intro and service-->
<div id=\"intro\" class=\"section scrollspy\">
    <div class=\"container\">
      <div class=\"row center-align\">
        <div class=\"col s12\"><h5>Select Timezone</h5></div>
      </div>
      <div class=\"row center-align\">
        <div class=\"col s3 m3 14\">
          <h5 class=\"promo-caption\"><a href=\"#!\" class=\"tzLinks tzLinks-all\">ALL</a></h5>
        </div>
        <div class=\"col s3 m3 14\">
          <h5 class=\"promo-caption\"><a href=\"#!\" id=\"tzEU\" class=\"tzLinks\">EU</a></h5>
        </div>
        <div class=\"col s3 m3 14\">
          <h5 class=\"promo-caption\"><a href=\"#!\" id=\"tzUS\" class=\"tzLinks\">US</a></h5>
        </div>
        <div class=\"col s3 m3 14\">
          <h5 class=\"promo-caption\"><a href=\"#!\" id=\"tzAU\" class=\"tzLinks\">AU</a></h5>
        </div>
      </div>
      <div class=\"row\">
          <div  class=\"col s12\">
            <div class=\"card material-table\">
              <div class=\"table-header\">
                <span class=\"table-title\">Sortable Ship Use Analysis and Doctrine/Fleet Composition Statistics - Search for and Click an Entity for Detailed Stats</span>
                <div class=\"actions\">
                  <a href=\"#!\" class=\"search-toggle waves-effect btn-flat nopadding\"><i class=\"material-icons\">search</i></a>
                </div>
              </div>
              <table id=\"stats\" class=\"stripe row-border order-column\" cellspacing=\"0\" width=\"100%\">
                <thead>
                  <tr>
                    <th data-field=\"rank\">#</th>
                    <th data-field=\"entityName\">Entity</th>
                    <th data-field=\"entityType\">Type</th>
                    <th data-field=\"iskKilled\">ISK Killed</th>
                    <th data-field=\"whKills\">WH Kills</th>

                    <th data-field=\"c1Kills\">C1 Kills</th>
                    <th data-field=\"c2Kills\">C2 Kills</th>
                    <th data-field=\"c3Kills\">C3 Kills</th>
                    <th data-field=\"c4Kills\">C4 Kills</th>
                    <th data-field=\"c5Kills\">C5 Kills</th>
                    <th data-field=\"c6Kills\">C6 Kills</th>
                    <th data-field=\"c7Kills\">Thera Kills</th>
                    <th data-field=\"c8Kills\">Shattered Kills</th>
                    <th data-field=\"c9Kills\">Frig Hole Kills</th>

                    <th data-field=\"t1Frig\">T1 Frig</th>
                    <th data-field=\"facFrig\">Faction Frig</th>
                    <th data-field=\"eAFrig\">Elc. Atk. Frig</th>
                    <th data-field=\"intFrig\">Interceptor</th>
                    <th data-field=\"aFFrig\">Ass. Frig</th>
                    <th data-field=\"logiFrig\">Logi Frig</th>
                    <th data-field=\"cOFrig\">Cov. Ops</th>
                    <th data-field=\"sBFrig\">Stealth Bomber</th>

                    <th data-field=\"t1Des\">T1 Des.</th>
                    <th data-field=\"interDes\">Interdictor</th>
                    <th data-field=\"cmdDes\">Cmd. Des.</th>
                    <th data-field=\"t3Des\">Tactical Des.</th>

                    <th data-field=\"t1Cru\">T1 Cruiser</th>
                    <th data-field=\"facCru\">Faction Cruiser</th>
                    <th data-field=\"recCru\">Recon</th>
                    <th data-field=\"hacCru\">HAC</th>
                    <th data-field=\"hIntCru\">H. Interdictor</th>
                    <th data-field=\"logiCru\">Logi. Cruiser</th>
                    <th data-field=\"t3Cru\">T3 Cruiser</th>

                    <th data-field=\"t1BC\">T1 BC</th>
                    <th data-field=\"facBC\">Faction BC</th>
                    <th data-field=\"cmdBC\">Cmd. Ship</th>

                    <th data-field=\"t1BS\">T1 BS</th>
                    <th data-field=\"facBS\">Faction BS</th>
                    <th data-field=\"marBS\">Marauder</th>
                    <th data-field=\"blopsBS\">Black Ops.</th>

                    <th data-field=\"carrierUse\">Carrier</th>
                    <th data-field=\"dreadUse\">Dread.</th>
                    <th data-field=\"faxUse\">Force Aux.</th>
                  </tr>
                </thead>
                <tbody id=\"dataTable\">

                </tbody>
              </table>
            </div>
          </div>
      </div>
    </div>
</div>

<!--Footer-->
<footer id=\"contact\" class=\"page-footer default_color scrollspy\">
    <div class=\"footer-copyright default_color\">
        <div class=\"container\">
            Made by <a class=\"white-text\" href=\"https://discord.gg/0keRDoXN2Cxw6PIY\" target=\"_blank\">Aekro</a> of <a class=\"white-text\" href=\"http://takeshis-castle.com/\" target=\"_blank\">[TAKSH]</a>. Thanks to <a class=\"white-text\" href=\"http://materializecss.com/\">materializecss</a>.
            Try <a class=\"white-text\" href=\"http://eve-vippy.com/\" target=\"_blank\">Vippy</a> as your next WH Mapper!
        </div>
    </div>
</footer>

  <!-- Compiled and minified JavaScript -->
  <script type=\"text/javascript\" src=\"https://code.jquery.com/jquery-2.1.1.min.js\"></script>
  <script type=\"text/javascript\" src=\"https://cdnjs.cloudflare.com/ajax/libs/modernizr/2.8.3/modernizr.min.js\" integrity=\"sha256-0rguYS0qgS6L4qVzANq4kjxPLtvnp5nn2nB5G1lWRv4=\" crossorigin=\"anonymous\"></script>
  <script src=\"https://cdnjs.cloudflare.com/ajax/libs/materialize/0.97.7/js/materialize.min.js\"></script>
  <script src=\"https://cdn.datatables.net/1.10.12/js/jquery.dataTables.min.js\"></script>

  <!--  Scripts-
  <script src=\"min/plugin-min.js\"></script>-->
  <script src=\"min/custom-min.js\"></script>
  <script src=\"/js/dataTable.js\"></script>
  <script src=\"js/entities.js\"></script>
";
    }

    public function getTemplateName()
    {
        return "entities.html";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  45 => 5,  42 => 4,  36 => 3,  30 => 2,  11 => 1,);
    }
}
/* {% extends "header.html" %}*/
/* {% block title %}WH Stats 2.0{% endblock %}*/
/* {% block version %}2.0{% endblock %}*/
/* {% block content %}*/
/* */
/* <!-- Modal Structure -->*/
/* <div id="modal1" class="modal">*/
/*   <div class="modal-content">*/
/*     <h4 class="modal-content-text">Loading Stats</h4>*/
/*     <i>This May Take A Few Seconds! Please Be Patient!</i><br>*/
/*     <div class="preloader-wrapper big active">*/
/*       <div class="spinner-layer spinner-blue-only">*/
/*         <div class="circle-clipper left">*/
/*           <div class="circle"></div>*/
/*         </div><div class="gap-patch">*/
/*           <div class="circle"></div>*/
/*         </div><div class="circle-clipper right">*/
/*           <div class="circle"></div>*/
/*         </div>*/
/*       </div>*/
/*     </div>*/
/*   </div>*/
/* </div>*/
/* <!--Intro and service-->*/
/* <div id="intro" class="section scrollspy">*/
/*     <div class="container">*/
/*       <div class="row center-align">*/
/*         <div class="col s12"><h5>Select Timezone</h5></div>*/
/*       </div>*/
/*       <div class="row center-align">*/
/*         <div class="col s3 m3 14">*/
/*           <h5 class="promo-caption"><a href="#!" class="tzLinks tzLinks-all">ALL</a></h5>*/
/*         </div>*/
/*         <div class="col s3 m3 14">*/
/*           <h5 class="promo-caption"><a href="#!" id="tzEU" class="tzLinks">EU</a></h5>*/
/*         </div>*/
/*         <div class="col s3 m3 14">*/
/*           <h5 class="promo-caption"><a href="#!" id="tzUS" class="tzLinks">US</a></h5>*/
/*         </div>*/
/*         <div class="col s3 m3 14">*/
/*           <h5 class="promo-caption"><a href="#!" id="tzAU" class="tzLinks">AU</a></h5>*/
/*         </div>*/
/*       </div>*/
/*       <div class="row">*/
/*           <div  class="col s12">*/
/*             <div class="card material-table">*/
/*               <div class="table-header">*/
/*                 <span class="table-title">Sortable Ship Use Analysis and Doctrine/Fleet Composition Statistics - Search for and Click an Entity for Detailed Stats</span>*/
/*                 <div class="actions">*/
/*                   <a href="#!" class="search-toggle waves-effect btn-flat nopadding"><i class="material-icons">search</i></a>*/
/*                 </div>*/
/*               </div>*/
/*               <table id="stats" class="stripe row-border order-column" cellspacing="0" width="100%">*/
/*                 <thead>*/
/*                   <tr>*/
/*                     <th data-field="rank">#</th>*/
/*                     <th data-field="entityName">Entity</th>*/
/*                     <th data-field="entityType">Type</th>*/
/*                     <th data-field="iskKilled">ISK Killed</th>*/
/*                     <th data-field="whKills">WH Kills</th>*/
/* */
/*                     <th data-field="c1Kills">C1 Kills</th>*/
/*                     <th data-field="c2Kills">C2 Kills</th>*/
/*                     <th data-field="c3Kills">C3 Kills</th>*/
/*                     <th data-field="c4Kills">C4 Kills</th>*/
/*                     <th data-field="c5Kills">C5 Kills</th>*/
/*                     <th data-field="c6Kills">C6 Kills</th>*/
/*                     <th data-field="c7Kills">Thera Kills</th>*/
/*                     <th data-field="c8Kills">Shattered Kills</th>*/
/*                     <th data-field="c9Kills">Frig Hole Kills</th>*/
/* */
/*                     <th data-field="t1Frig">T1 Frig</th>*/
/*                     <th data-field="facFrig">Faction Frig</th>*/
/*                     <th data-field="eAFrig">Elc. Atk. Frig</th>*/
/*                     <th data-field="intFrig">Interceptor</th>*/
/*                     <th data-field="aFFrig">Ass. Frig</th>*/
/*                     <th data-field="logiFrig">Logi Frig</th>*/
/*                     <th data-field="cOFrig">Cov. Ops</th>*/
/*                     <th data-field="sBFrig">Stealth Bomber</th>*/
/* */
/*                     <th data-field="t1Des">T1 Des.</th>*/
/*                     <th data-field="interDes">Interdictor</th>*/
/*                     <th data-field="cmdDes">Cmd. Des.</th>*/
/*                     <th data-field="t3Des">Tactical Des.</th>*/
/* */
/*                     <th data-field="t1Cru">T1 Cruiser</th>*/
/*                     <th data-field="facCru">Faction Cruiser</th>*/
/*                     <th data-field="recCru">Recon</th>*/
/*                     <th data-field="hacCru">HAC</th>*/
/*                     <th data-field="hIntCru">H. Interdictor</th>*/
/*                     <th data-field="logiCru">Logi. Cruiser</th>*/
/*                     <th data-field="t3Cru">T3 Cruiser</th>*/
/* */
/*                     <th data-field="t1BC">T1 BC</th>*/
/*                     <th data-field="facBC">Faction BC</th>*/
/*                     <th data-field="cmdBC">Cmd. Ship</th>*/
/* */
/*                     <th data-field="t1BS">T1 BS</th>*/
/*                     <th data-field="facBS">Faction BS</th>*/
/*                     <th data-field="marBS">Marauder</th>*/
/*                     <th data-field="blopsBS">Black Ops.</th>*/
/* */
/*                     <th data-field="carrierUse">Carrier</th>*/
/*                     <th data-field="dreadUse">Dread.</th>*/
/*                     <th data-field="faxUse">Force Aux.</th>*/
/*                   </tr>*/
/*                 </thead>*/
/*                 <tbody id="dataTable">*/
/* */
/*                 </tbody>*/
/*               </table>*/
/*             </div>*/
/*           </div>*/
/*       </div>*/
/*     </div>*/
/* </div>*/
/* */
/* <!--Footer-->*/
/* <footer id="contact" class="page-footer default_color scrollspy">*/
/*     <div class="footer-copyright default_color">*/
/*         <div class="container">*/
/*             Made by <a class="white-text" href="https://discord.gg/0keRDoXN2Cxw6PIY" target="_blank">Aekro</a> of <a class="white-text" href="http://takeshis-castle.com/" target="_blank">[TAKSH]</a>. Thanks to <a class="white-text" href="http://materializecss.com/">materializecss</a>.*/
/*             Try <a class="white-text" href="http://eve-vippy.com/" target="_blank">Vippy</a> as your next WH Mapper!*/
/*         </div>*/
/*     </div>*/
/* </footer>*/
/* */
/*   <!-- Compiled and minified JavaScript -->*/
/*   <script type="text/javascript" src="https://code.jquery.com/jquery-2.1.1.min.js"></script>*/
/*   <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/modernizr/2.8.3/modernizr.min.js" integrity="sha256-0rguYS0qgS6L4qVzANq4kjxPLtvnp5nn2nB5G1lWRv4=" crossorigin="anonymous"></script>*/
/*   <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.97.7/js/materialize.min.js"></script>*/
/*   <script src="https://cdn.datatables.net/1.10.12/js/jquery.dataTables.min.js"></script>*/
/* */
/*   <!--  Scripts-*/
/*   <script src="min/plugin-min.js"></script>-->*/
/*   <script src="min/custom-min.js"></script>*/
/*   <script src="/js/dataTable.js"></script>*/
/*   <script src="js/entities.js"></script>*/
/* {% endblock %}*/
/* */
