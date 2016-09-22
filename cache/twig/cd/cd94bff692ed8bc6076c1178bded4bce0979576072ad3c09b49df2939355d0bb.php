<?php

/* entity.html */
class __TwigTemplate_ccefadd032122080d7b261b47350f1896337bce790dbd638c33f93efbf1bb3d2 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        // line 1
        $this->parent = $this->loadTemplate("header.html", "entity.html", 1);
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

<!--Essential Stats-->
<div id=\"essentialStats\" class=\"section scrollspy\">
    <div class=\"container\">
      <div class=\"row\">
        <div  class=\"col s12\">
          <h2 class=\"center header text_h2\"><a href=\"\" target=\"_blank\" id=\"entityName\"></a></h2>
        </div>
      </div>
      <div class=\"row center-align\">
        <div class=\"col s4 m4 14\">
          <h5 class=\"promo-caption totals-header\">Total Kills <span class=\"totals totalKills\"></span></h5>
        </div>
        <div class=\"col s4 m4 14\">
          <h5 class=\"promo-caption totals-header\">Total ISK <span class=\"totals totalISK\"></span></h5>
        </div>
        <div class=\"col s4 m4 14\">
          <h5 class=\"promo-caption totals-header\">Average Fleet Size <span class=\"totals avgPilots\"></span></h5>
        </div>
      </div>
    </div>
</div>

<!--Big Graphs-->
<div id=\"bigGraphs\" class=\"section scrollspy\">
  <div class=\"container\">
    <div class=\"row\">
      <div class=\"col s12\">
        <h5 class=\"promo-caption periodStats\">Total Kills</h5>
        <div class=\"big-chart-holder\">
          <canvas id=\"chartKills\" width=\"400\" height=\"400\"></canvas>
        </div>
      </div>
    </div>
    <div class=\"row\">
      <div class=\"col s12\">
        <h5 class=\"promo-caption\">ISK Killed (Billions)</h5>
        <div class=\"big-chart-holder\">
          <canvas id=\"chartISK\" width=\"400\" height=\"400\"></canvas>
        </div>
      </div>
    </div>
  </div>
</div>

<!--Breakdowns-->
<div id=\"breakdowns\" class=\"section scrollspy\">
    <div class=\"container\">
        <h2 class=\"header text_b\">Ship Usage Breakdowns</h2>
        <div class=\"row\">
            <div class=\"col s12 center\"><i>Represents Ships Used by this Entity, NOT KILLS!</i></div>
            <div class=\"col s12\">
                <div class=\"breakdowns-holder\">
                  <canvas id=\"chartBreakdowns\" width=\"400\" height=\"1000\"></canvas>
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

  <!--  Scripts-
  <script src=\"min/plugin-min.js\"></script>-->
  <script src=\"../min/custom-min.js\"></script>
  <script src=\"../js/entity.js\"></script>
";
    }

    public function getTemplateName()
    {
        return "entity.html";
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
/* */
/* <!--Essential Stats-->*/
/* <div id="essentialStats" class="section scrollspy">*/
/*     <div class="container">*/
/*       <div class="row">*/
/*         <div  class="col s12">*/
/*           <h2 class="center header text_h2"><a href="" target="_blank" id="entityName"></a></h2>*/
/*         </div>*/
/*       </div>*/
/*       <div class="row center-align">*/
/*         <div class="col s4 m4 14">*/
/*           <h5 class="promo-caption totals-header">Total Kills <span class="totals totalKills"></span></h5>*/
/*         </div>*/
/*         <div class="col s4 m4 14">*/
/*           <h5 class="promo-caption totals-header">Total ISK <span class="totals totalISK"></span></h5>*/
/*         </div>*/
/*         <div class="col s4 m4 14">*/
/*           <h5 class="promo-caption totals-header">Average Fleet Size <span class="totals avgPilots"></span></h5>*/
/*         </div>*/
/*       </div>*/
/*     </div>*/
/* </div>*/
/* */
/* <!--Big Graphs-->*/
/* <div id="bigGraphs" class="section scrollspy">*/
/*   <div class="container">*/
/*     <div class="row">*/
/*       <div class="col s12">*/
/*         <h5 class="promo-caption periodStats">Total Kills</h5>*/
/*         <div class="big-chart-holder">*/
/*           <canvas id="chartKills" width="400" height="400"></canvas>*/
/*         </div>*/
/*       </div>*/
/*     </div>*/
/*     <div class="row">*/
/*       <div class="col s12">*/
/*         <h5 class="promo-caption">ISK Killed (Billions)</h5>*/
/*         <div class="big-chart-holder">*/
/*           <canvas id="chartISK" width="400" height="400"></canvas>*/
/*         </div>*/
/*       </div>*/
/*     </div>*/
/*   </div>*/
/* </div>*/
/* */
/* <!--Breakdowns-->*/
/* <div id="breakdowns" class="section scrollspy">*/
/*     <div class="container">*/
/*         <h2 class="header text_b">Ship Usage Breakdowns</h2>*/
/*         <div class="row">*/
/*             <div class="col s12 center"><i>Represents Ships Used by this Entity, NOT KILLS!</i></div>*/
/*             <div class="col s12">*/
/*                 <div class="breakdowns-holder">*/
/*                   <canvas id="chartBreakdowns" width="400" height="1000"></canvas>*/
/*                 </div>*/
/*             </div>*/
/*         </div>*/
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
/* */
/*   <!--  Scripts-*/
/*   <script src="min/plugin-min.js"></script>-->*/
/*   <script src="../min/custom-min.js"></script>*/
/*   <script src="../js/entity.js"></script>*/
/* {% endblock %}*/
/* */
