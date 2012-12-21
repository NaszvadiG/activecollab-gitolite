<tr class="{cycle values='odd,even'}">
  <td class="star">{favorite_object object=$repository user=$logged_user}</td>
  <td class="graph">
     
  {assign var=activity value=$repository->source_repository->getRecentActivity()}
  {if is_foreachable($activity)}
    <ul class="timeline">
    {foreach from=$activity item=item}
      <li>
        <a href="#" title="{lang commits=$item.commits day=$item.created_on}:commits commits on :day{/lang}" onclick="return false;">
          <span class="count" style="height:{$item.percentage}%"></span>
        </a>
      </li>
    {/foreach} 
    </ul>
  {/if}
  </td>
  <td class="name">

    <strong>
      <img alt="{$repository->source_repository->getVerboseName()}"
           src="{image_url name='icons/16x16/'|cat:$repository->source_repository->getIconFileName() module=$smarty.const.SOURCE_MODULE}"
           title="{$repository->source_repository->getVerboseName()}"
      />
      {object_link object=$repository}
    </strong>

    <span class="block details">
    {if $repository->getId()|in_array:$gitolite_repos || $repository->getId()|in_array:$remote_repos}
        <code>{$repository->getBody()}</code>
     {else}
       <a href="{$repository->getBody()}">{$repository->getBody()}</a>
    {/if}
     </span>
  </td>
  <td class="last_commit">
    {assign var=last_commit value=$repository->source_repository->getLastCommit()}
    {if $last_commit instanceof SourceCommit}
      <strong>{substr($last_commit->getName(),0,8)}</strong><br />
      {$last_commit->getAuthor() nofilter} {lang}on{/lang} {$last_commit->getCommitedOn()|date:0}
    {else}
      -
    {/if}          
  </td>
  <td class="star">{object_subscription object=$repository user=$logged_user}</td>
  <td class="visibility">{object_visibility object=$repository user=$logged_user}</td>
</tr>