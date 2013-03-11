..  Content substitution
	...................................................
	Hint: following expression |my_substition_value| will be replaced when rendering doc.

.. |author| replace:: Bernhard Schmitt <b.schmitt@core4.de>
.. |extension_key| replace:: form_base
.. |extension_name| replace:: Form Base
.. |extension_version| replace:: 1.0.0
.. |typo3| image:: Images/Typo3.png
.. |time| date:: %m-%d-%Y %H:%M
.. |now| date:: %H:%M

..  Custom roles
	...................................................
	After declaring a role like this: ".. role:: custom", the document may use the new role like :custom:`interpreted text`. 
	Basically, this will wrap the content with a CSS class to be styled in a special way when document get rendered.
	More information: http://docutils.sourceforge.net/docs/ref/rst/roles.html

.. role:: code
.. role:: typoscript
.. role:: typoscript(code)
.. role:: ts(typoscript)
.. role:: php(code)