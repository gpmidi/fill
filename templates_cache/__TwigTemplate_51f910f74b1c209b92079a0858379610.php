<?php

/* header.html */
class __TwigTemplate_51f910f74b1c209b92079a0858379610 extends Twig_Template
{
    public function display(array $context, array $blocks = array())
    {
        $context = array_merge($this->env->getGlobals(), $context);

        // line 1
        echo "<!DOCTYPE html>
<html>
\t<head>
\t\t<title>";
        // line 4
        echo twig_escape_filter($this->env, (isset($context['HR_TEMPLATE_TITLE']) ? $context['HR_TEMPLATE_TITLE'] : null), "html");
        echo " &lsaquo; Fill the Bukkit</title>

\t\t<link href='http://fonts.googleapis.com/css?family=Nobile' rel='stylesheet' type='text/css'>
\t\t<link rel=\"stylesheet\" type=\"text/css\" href=\"";
        // line 7
        echo twig_escape_filter($this->env, (isset($context['HR_TEMPLATE_PUB_ROOT']) ? $context['HR_TEMPLATE_PUB_ROOT'] : null), "html");
        echo "css/fonts.css\" />
\t\t<link rel=\"stylesheet\" type=\"text/css\" href=\"";
        // line 8
        echo twig_escape_filter($this->env, (isset($context['HR_TEMPLATE_PUB_ROOT']) ? $context['HR_TEMPLATE_PUB_ROOT'] : null), "html");
        echo "css/fill.css\" />
\t\t<link rel=\"stylesheet\" type=\"text/css\" href=\"";
        // line 9
        echo twig_escape_filter($this->env, (isset($context['HR_TEMPLATE_PUB_ROOT']) ? $context['HR_TEMPLATE_PUB_ROOT'] : null), "html");
        echo "css/messages.css\" />
\t\t<script type=\"text/javascript\" src=\"https://ajax.googleapis.com/ajax/libs/jquery/1.4.4/jquery.min.js\"></script>
\t</head>
\t<body>
\t<div class=\"wrap\">
\t\t<div class=\"header\">
\t\t\t<div class=\"menu-links\">
\t\t\t\t<a class=\"home-button\" href=\"/\"></a>
\t\t\t\t<ul id='nav'>
\t\t\t\t";
        // line 18
        $context['_parent'] = (array) $context;
        $context['_seq'] = twig_ensure_traversable((isset($context['HR_MENU_ITEMS']) ? $context['HR_MENU_ITEMS'] : null));
        foreach ($context['_seq'] as $context['_key'] => $context['menu_item']) {
            // line 19
            echo "
\t\t\t\t\t<li id=\"";
            // line 20
            echo twig_escape_filter($this->env, $this->getAttribute((isset($context['menu_item']) ? $context['menu_item'] : null), "id", array(), "any"), "html");
            echo "\" class=\"";
            echo twig_escape_filter($this->env, $this->getAttribute((isset($context['menu_item']) ? $context['menu_item'] : null), "class", array(), "any"), "html");
            echo "\">
\t\t\t\t\t\t<a href=\"";
            // line 21
            echo twig_escape_filter($this->env, $this->getAttribute((isset($context['menu_item']) ? $context['menu_item'] : null), "uri", array(), "any"), "html");
            echo "\">";
            echo twig_escape_filter($this->env, $this->getAttribute((isset($context['menu_item']) ? $context['menu_item'] : null), "text", array(), "any"), "html");
            echo "</a>
\t\t\t\t\t</li>

\t\t\t\t";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['menu_item'], $context['_parent'], $context['loop']);
        $context = array_merge($_parent, array_intersect_key($context, $_parent));
        // line 25
        echo "
\t\t\t\t</ul>
\t\t\t</div>
\t\t</div>";
    }

    public function getTemplateName()
    {
        return "header.html";
    }
}
