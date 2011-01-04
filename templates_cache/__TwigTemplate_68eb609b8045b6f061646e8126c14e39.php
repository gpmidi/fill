<?php

/* index.html */
class __TwigTemplate_68eb609b8045b6f061646e8126c14e39 extends Twig_Template
{
    public function display(array $context, array $blocks = array())
    {
        $context = array_merge($this->env->getGlobals(), $context);

        // line 1
        echo "\t\t";
        $this->env->loadTemplate("header.html")->display($context);
        // line 2
        echo "\t        <div class=\"featured-rotator\">
\t            <div class=\"featured-rotator-wrap\">
\t\t\t\t\t<a href=\"#prev\" class=\"rprev\"></a>
\t\t\t\t\t<ul>
\t\t\t\t\t\t<li>featured item 1</li>
\t\t\t\t\t\t<li>featured item 2</li>
\t\t\t\t\t\t<li>featured item 3</li>
\t\t\t\t\t\t<li>featured item 4</li>
\t\t\t\t\t</ul>
\t\t\t\t\t<a href=\"#next\" class=\"rnext\"></a>
\t            </div>
\t        </div>
\t\t<div class=\"content-wrap\">
\t\t\t<div class=\"cols1\">
\t\t\t\t<div class=\"item\">
\t\t\t\t\t<div class=\"item-t\">
\t\t\t\t\t\t<h1>Categories</h1>
\t\t\t\t\t</div>
\t\t\t\t</div>
\t\t\t</div>
\t\t\t<div class=\"cols2\">
\t\t\t\t<div class=\"item\">
\t\t\t\t\t<div class=\"item-t\">
\t\t\t\t\t\t<h1>";
        // line 25
        echo (isset($context['HR_TEMPLATE_CONTENT_HEADER']) ? $context['HR_TEMPLATE_CONTENT_HEADER'] : null);
        echo "</h1>
\t\t\t\t\t\t";
        // line 26
        echo (isset($context['HR_TEMPLATE_CONTENT']) ? $context['HR_TEMPLATE_CONTENT'] : null);
        echo "
\t\t\t\t\t</div>
\t\t\t\t</div>
\t\t\t</div>
\t\t\t<div class=\"cols3\">
\t\t\t\t<div class=\"item\">
\t\t\t\t\t<div class=\"item-t\">
\t\t\t\t\t\t<h1>Categories</h1>
\t\t\t\t\t</div>
\t\t\t\t</div>
\t\t\t</div>
\t\t</div>
\t\t";
        // line 38
        $this->env->loadTemplate("footer.html")->display($context);
    }

    public function getTemplateName()
    {
        return "index.html";
    }
}
