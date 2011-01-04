<?php

/* footer.html */
class __TwigTemplate_1b626b089707a2972cef405a602228b0 extends Twig_Template
{
    public function display(array $context, array $blocks = array())
    {
        $context = array_merge($this->env->getGlobals(), $context);

        // line 1
        echo "\t\t<div id=\"footer\">
\t\t\t<p>&copy; <?php echo date('Y'); ?> the Bukkit Team.</p>
\t\t\t<p>Powered by <a href=\"http://hostiio.com\">Hostiio</a> and <a href=\"http://aws.amazon.com/s3\">Amazon S3</a>.</p>
\t\t\t<p>Git Revision: <a href=\"http://github.com/robbiet480/hRepo/commit/<?php echo \$gitCommit['long']; ?>\"><?php echo \$gitCommit['short']; ?></a> - by <?php echo \$gitCommit['userid']; ?> at <?php echo date('jS M Y, H:i:s', strtotime(\$gitCommit['commitdate'])); ?></p>
\t\t</div>

\t</body>
</html>";
    }

    public function getTemplateName()
    {
        return "footer.html";
    }
}
