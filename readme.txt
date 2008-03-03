=== TagMahal - Automatic tag suggester ===
Contributors: www.flaptor.com
Tags: autotagging, tagging, tags, suggestions, tag, keywords, Post, suggest, auto, automatic
Requires at least: 2.0.5
Tested up to: 2.3.3
Stable tag: 1.0.2

Suggests tags for a post using the Flaptor Tagger API

== Description ==

Tag suggester for post content using state of the art machine learning
algorithms for semantic analysis. It works in a toolbox in the sidebar. 
It provides a button that connects to the Flaptor Tagger API through HTTP, 
retrieves a list of possible tags and displays them in the toolbox. You can add any of the 
suggested tags by clicking on it, or get new suggestions using the 
Refresh Tags button if the text of the post has changed.

== Screenshots ==

1. Main screenshot

== Frequently Asked Questions ==

= How does TagMahal find tags for a post? =

TagMahal uses the Flaptor Autotagger API - [Flaptor Autotagger](http://tagger.flaptor.com "Autotagger Homepage") - to find tags for a post. 
Flaptor Autotagger uses a learning algorithm based on a set of training documents tagged by humans.

= What languages does it support? =

Currently, Flaptor Autotagger only supports English. 
We are working on other languages, Spanish is next on our list.

= I'm having problems with the plugin: it finds no tags or doesn't seem to update the tags when I change the post text =

If you're using a custom post editor (FCKEditor for instance) TagMahal won't be able to read your posts text
until you save it.  

== Installation ==

Copy the tagMahal.php into your wordpress plugins folder.
Enable the plugin.