<% if Categories %>
Tagged: <% loop Categories %><a href="{$Top.Parent.Link}Tag/$Title">$Title</a><% if Last %><% else %>, <% end_if %><% end_loop %>
<% end_if %>