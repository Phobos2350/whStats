<?php

/* header.html */
class __TwigTemplate_04a3e1dc154a021b87a52df360a96f471eee1bca0234f5a7b1f1f5ca761e40c0 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = array(
            'title' => array($this, 'block_title'),
            'version' => array($this, 'block_version'),
            'content' => array($this, 'block_content'),
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        // line 1
        echo "<!DOCTYPE html>
<html>
<head>
    <meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\"/>
    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1, maximum-scale=1.0, user-scalable=no\"/>
    <meta name=\"theme-color\" content=\"#F7F7F7\">
    <title>";
        // line 7
        $this->displayBlock('title', $context, $blocks);
        echo "</title>

    <!-- Chart.js -->
    <script src=\"https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.2.2/Chart.bundle.min.js\"></script>

    <!--Import Google Icon Font-->
    <link href=\"http://fonts.googleapis.com/icon?family=Material+Icons\" rel=\"stylesheet\">
    <!-- Compiled and minified CSS -->
    <link rel=\"stylesheet\" href=\"https://cdnjs.cloudflare.com/ajax/libs/materialize/0.97.7/css/materialize.min.css\">

    <!-- CSS  -->
    <!--<link href=\"min/plugin-min.css\" type=\"text/css\" rel=\"stylesheet\">-->
    <link href=\"min/custom-min.css\" type=\"text/css\" rel=\"stylesheet\" >
    <link href=\"css/style.css\" type=\"text/css\" rel=\"stylesheet\">
    <link href=\"css/dataTable.css\" type=\"text/css\" rel=\"stylesheet\">

</head>
<body id=\"top\" class=\"scrollspy\">

<!-- Pre Loader -->
<div id=\"loader-wrapper\">
    <div id=\"loader\"></div>

    <div class=\"loader-section section-left\"></div>
    <div class=\"loader-section section-right\"></div>

</div>

<!--Navigation-->
 <div class=\"navbar-fixed main-nav\">
    <nav id=\"nav_f\" class=\"default_color\" role=\"navigation\">
        <div class=\"container\">
            <div class=\"nav-wrapper\">
            <a href=\"#\" id=\"logo-container\" class=\"brand-logo\">WH STATS <span class=\"logo-version\">";
        // line 40
        $this->displayBlock('version', $context, $blocks);
        echo "</span></a>
                <ul class=\"right hide-on-med-and-down\">
                  <li><a href=\"../\">Stats</a></li>
                  <li><a href=\"../entities\">Entities</a></li>
                  <li><a href=\"../pilots\">Pilots</a></li>
                </ul>
                <ul id=\"nav-mobile\" class=\"side-nav\">
                  <li><a href=\"../\">Stats</a></li>
                  <li><a href=\"../entities\">Entities</a></li>
                  <li><a href=\"../pilots\">Pilots</a></li>
                </ul>
                <a href=\"#\" data-activates=\"nav-mobile\" class=\"button-collapse\"><i class=\"mdi-navigation-menu\"></i></a>
            </div>
        </div>
    </nav>
</div>
<div class=\"navbar-fixed\">
  <nav id=\"nav_f\" class=\"blue-grey lighten-2\" role=\"navigation\">
    <div class=\"container center center-align\">
      <div class=\"nav-wrapper center-align valign-wrapper\">
        <ul class=\"center-align valign\">
            <li><a href=\"#!\" class=\"periodLinks periodLinks-hour\">Hour</a></li>
            <li><a href=\"#!\" class=\"periodLinks\">Day</a></li>
            <li><a href=\"#!\" class=\"periodLinks\">Week</a></li>
            <li><a href=\"#!\" class=\"periodLinks breadcrumb\">Month</a></li>
            <li class=\"hide-on-small-only\"><a href=\"#!\" class=\"period\"></a></li>
            <li class=\"hide-on-small-only\"><a href=\"#!\" class=\"monthLinks prevMonth hide\">&lt;&lt; Previous</a></li>
            <li class=\"hide-on-small-only\"><a href=\"#!\" class=\"monthLinks currMonth hide\">Month</a></li>
            <li class=\"hide-on-small-only\"><a href=\"#!\" class=\"monthLinks nextMonth hide\"></a></li>
        </ul>
      </div>
    </div>
  </nav>
</div>
<div class=\"navbar-fixed hide-on-med-and-up\">
  <nav id=\"nav_f\" class=\"blue-grey lighten-2\" role=\"navigation\">
    <div class=\"container center center-align\">
      <div class=\"nav-wrapper center-align valign-wrapper\">
        <ul class=\"center-align valign\">
            <li><a href=\"#!\" class=\"period\"></a></li>
            <li><a href=\"#!\" class=\"monthLinks prevMonth hide\">&lt;&lt;Previous</a></li>
            <li><a href=\"#!\" class=\"monthLinks currMonth hide\">Month</a></li>
            <li><a href=\"#!\" class=\"monthLinks nextMonth hide\"></a></li>
        </ul>
      </div>
    </div>
  </nav>
</div>

<!--Hero-->
<div class=\"section\" id=\"index-banner\">
    <div class=\"container\">
        <h1 class=\"text_h center header cd-headline letters type\">
            <span>WH Stats 2.0 </span>
            <span class=\"cd-words-wrapper waiting\">
                <b class=\"is-visible\">more accurate</b>
                <b>more kills</b>
                <b>more stats</b>
            </span>
        </h1>
    </div>
</div>
";
        // line 102
        $this->displayBlock('content', $context, $blocks);
        // line 103
        echo "</body>
</html>
";
    }

    // line 7
    public function block_title($context, array $blocks = array())
    {
    }

    // line 40
    public function block_version($context, array $blocks = array())
    {
    }

    // line 102
    public function block_content($context, array $blocks = array())
    {
    }

    public function getTemplateName()
    {
        return "header.html";
    }

    public function getDebugInfo()
    {
        return array (  149 => 102,  144 => 40,  139 => 7,  133 => 103,  131 => 102,  66 => 40,  30 => 7,  22 => 1,);
    }
}
/* <!DOCTYPE html>*/
/* <html>*/
/* <head>*/
/*     <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>*/
/*     <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0, user-scalable=no"/>*/
/*     <meta name="theme-color" content="#F7F7F7">*/
/*     <title>{% block title %}{% endblock %}</title>*/
/* */
/*     <!-- Chart.js -->*/
/*     <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.2.2/Chart.bundle.min.js"></script>*/
/* */
/*     <!--Import Google Icon Font-->*/
/*     <link href="http://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">*/
/*     <!-- Compiled and minified CSS -->*/
/*     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.97.7/css/materialize.min.css">*/
/* */
/*     <!-- CSS  -->*/
/*     <!--<link href="min/plugin-min.css" type="text/css" rel="stylesheet">-->*/
/*     <link href="min/custom-min.css" type="text/css" rel="stylesheet" >*/
/*     <link href="css/style.css" type="text/css" rel="stylesheet">*/
/*     <link href="css/dataTable.css" type="text/css" rel="stylesheet">*/
/* */
/* </head>*/
/* <body id="top" class="scrollspy">*/
/* */
/* <!-- Pre Loader -->*/
/* <div id="loader-wrapper">*/
/*     <div id="loader"></div>*/
/* */
/*     <div class="loader-section section-left"></div>*/
/*     <div class="loader-section section-right"></div>*/
/* */
/* </div>*/
/* */
/* <!--Navigation-->*/
/*  <div class="navbar-fixed main-nav">*/
/*     <nav id="nav_f" class="default_color" role="navigation">*/
/*         <div class="container">*/
/*             <div class="nav-wrapper">*/
/*             <a href="#" id="logo-container" class="brand-logo">WH STATS <span class="logo-version">{% block version %}{% endblock %}</span></a>*/
/*                 <ul class="right hide-on-med-and-down">*/
/*                   <li><a href="../">Stats</a></li>*/
/*                   <li><a href="../entities">Entities</a></li>*/
/*                   <li><a href="../pilots">Pilots</a></li>*/
/*                 </ul>*/
/*                 <ul id="nav-mobile" class="side-nav">*/
/*                   <li><a href="../">Stats</a></li>*/
/*                   <li><a href="../entities">Entities</a></li>*/
/*                   <li><a href="../pilots">Pilots</a></li>*/
/*                 </ul>*/
/*                 <a href="#" data-activates="nav-mobile" class="button-collapse"><i class="mdi-navigation-menu"></i></a>*/
/*             </div>*/
/*         </div>*/
/*     </nav>*/
/* </div>*/
/* <div class="navbar-fixed">*/
/*   <nav id="nav_f" class="blue-grey lighten-2" role="navigation">*/
/*     <div class="container center center-align">*/
/*       <div class="nav-wrapper center-align valign-wrapper">*/
/*         <ul class="center-align valign">*/
/*             <li><a href="#!" class="periodLinks periodLinks-hour">Hour</a></li>*/
/*             <li><a href="#!" class="periodLinks">Day</a></li>*/
/*             <li><a href="#!" class="periodLinks">Week</a></li>*/
/*             <li><a href="#!" class="periodLinks breadcrumb">Month</a></li>*/
/*             <li class="hide-on-small-only"><a href="#!" class="period"></a></li>*/
/*             <li class="hide-on-small-only"><a href="#!" class="monthLinks prevMonth hide">&lt;&lt; Previous</a></li>*/
/*             <li class="hide-on-small-only"><a href="#!" class="monthLinks currMonth hide">Month</a></li>*/
/*             <li class="hide-on-small-only"><a href="#!" class="monthLinks nextMonth hide"></a></li>*/
/*         </ul>*/
/*       </div>*/
/*     </div>*/
/*   </nav>*/
/* </div>*/
/* <div class="navbar-fixed hide-on-med-and-up">*/
/*   <nav id="nav_f" class="blue-grey lighten-2" role="navigation">*/
/*     <div class="container center center-align">*/
/*       <div class="nav-wrapper center-align valign-wrapper">*/
/*         <ul class="center-align valign">*/
/*             <li><a href="#!" class="period"></a></li>*/
/*             <li><a href="#!" class="monthLinks prevMonth hide">&lt;&lt;Previous</a></li>*/
/*             <li><a href="#!" class="monthLinks currMonth hide">Month</a></li>*/
/*             <li><a href="#!" class="monthLinks nextMonth hide"></a></li>*/
/*         </ul>*/
/*       </div>*/
/*     </div>*/
/*   </nav>*/
/* </div>*/
/* */
/* <!--Hero-->*/
/* <div class="section" id="index-banner">*/
/*     <div class="container">*/
/*         <h1 class="text_h center header cd-headline letters type">*/
/*             <span>WH Stats 2.0 </span>*/
/*             <span class="cd-words-wrapper waiting">*/
/*                 <b class="is-visible">more accurate</b>*/
/*                 <b>more kills</b>*/
/*                 <b>more stats</b>*/
/*             </span>*/
/*         </h1>*/
/*     </div>*/
/* </div>*/
/* {% block content %}{% endblock %}*/
/* </body>*/
/* </html>*/
/* */
