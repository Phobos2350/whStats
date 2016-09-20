<?php

/* noMonth.html */
class __TwigTemplate_188a5f48086071c0292b01bcaf5edea6f8dbb39ba78ff8887b703ded8055d64a extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = array(
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        // line 1
        echo "<!DOCTYPE html>
<html class=\"splash\">
<head>
    <meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\"/>
    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1, maximum-scale=1.0, user-scalable=no\"/>
    <meta name=\"theme-color\" content=\"#2196F3\">
    <title>No Data!</title>
    <!--Import Google Icon Font-->
    <link href=\"http://fonts.googleapis.com/icon?family=Material+Icons\" rel=\"stylesheet\">
    <!-- Compiled and minified CSS -->
    <link rel=\"stylesheet\" href=\"https://cdnjs.cloudflare.com/ajax/libs/materialize/0.97.7/css/materialize.min.css\">

    <!-- CSS  -->
    <!--<link href=\"min/plugin-min.css\" type=\"text/css\" rel=\"stylesheet\">-->
    <link href=\"/min/custom-min.css\" type=\"text/css\" rel=\"stylesheet\" >
    <link href=\"/css/style.css\" type=\"text/css\" rel=\"stylesheet\">

</head>
<body class=\"splash\">
  <div class=\"section no-pad-bot\" id=\"index-banner-maint\">
    <div class=\"container\">
      <h1 class=\"text_h center header cd-headline\">
        <span>No Data For ";
        // line 23
        echo twig_escape_filter($this->env, (isset($context["month"]) ? $context["month"] : null), "html", null, true);
        echo " / ";
        echo twig_escape_filter($this->env, (isset($context["year"]) ? $context["year"] : null), "html", null, true);
        echo "</span><br>
        <span>A Stats Generation Task Has Been Despatched!</span>
      </h1>
    </div>
  </div>
</body>
</html>
";
    }

    public function getTemplateName()
    {
        return "noMonth.html";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  43 => 23,  19 => 1,);
    }
}
/* <!DOCTYPE html>*/
/* <html class="splash">*/
/* <head>*/
/*     <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>*/
/*     <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0, user-scalable=no"/>*/
/*     <meta name="theme-color" content="#2196F3">*/
/*     <title>No Data!</title>*/
/*     <!--Import Google Icon Font-->*/
/*     <link href="http://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">*/
/*     <!-- Compiled and minified CSS -->*/
/*     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.97.7/css/materialize.min.css">*/
/* */
/*     <!-- CSS  -->*/
/*     <!--<link href="min/plugin-min.css" type="text/css" rel="stylesheet">-->*/
/*     <link href="/min/custom-min.css" type="text/css" rel="stylesheet" >*/
/*     <link href="/css/style.css" type="text/css" rel="stylesheet">*/
/* */
/* </head>*/
/* <body class="splash">*/
/*   <div class="section no-pad-bot" id="index-banner-maint">*/
/*     <div class="container">*/
/*       <h1 class="text_h center header cd-headline">*/
/*         <span>No Data For {{ month }} / {{ year }}</span><br>*/
/*         <span>A Stats Generation Task Has Been Despatched!</span>*/
/*       </h1>*/
/*     </div>*/
/*   </div>*/
/* </body>*/
/* </html>*/
/* */
