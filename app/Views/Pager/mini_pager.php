<?php $pager->setSurroundCount(2) ?>

<nav aria-label="Page navigation">
    <ul class="inline-flex items-center -space-x-px">
        <?php if ($pager->hasPrevious()) : ?>
            <li>
                <a href="<?= $pager->getFirst() ?>" aria-label="<?= lang('Pager.first') ?>" class="block px-2 py-1 ml-0 leading-tight text-slate-500 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-l-lg hover:bg-slate-100 dark:hover:text-white text-[10px]">
                    <span aria-hidden="true">&laquo;</span>
                </a>
            </li>
            <li>
                <a href="<?= $pager->getPrevious() ?>" aria-label="<?= lang('Pager.previous') ?>" class="block px-2 py-1 leading-tight text-slate-500 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 hover:bg-slate-100 dark:hover:text-white text-[10px]">
                    <span aria-hidden="true">&lsaquo;</span>
                </a>
            </li>
        <?php endif ?>

        <?php foreach ($pager->links() as $link) : ?>
            <li>
                <a href="<?= $link['uri'] ?>" class="<?= $link['active'] ? 'z-10 px-2 py-1 leading-tight text-indigo-600 border border-indigo-300 bg-indigo-50 hover:bg-indigo-100 hover:text-indigo-700 dark:border-slate-700 dark:bg-slate-700 dark:text-white' : 'px-2 py-1 leading-tight text-slate-500 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 hover:bg-slate-100 dark:hover:text-white' ?> text-[10px]">
                    <?= $link['title'] ?>
                </a>
            </li>
        <?php endforeach ?>

        <?php if ($pager->hasNext()) : ?>
            <li>
                <a href="<?= $pager->getNext() ?>" aria-label="<?= lang('Pager.next') ?>" class="block px-2 py-1 leading-tight text-slate-500 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 hover:bg-slate-100 dark:hover:text-white text-[10px]">
                    <span aria-hidden="true">&rsaquo;</span>
                </a>
            </li>
            <li>
                <a href="<?= $pager->getLast() ?>" aria-label="<?= lang('Pager.last') ?>" class="block px-2 py-1 leading-tight text-slate-500 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-r-lg hover:bg-slate-100 dark:hover:text-white text-[10px]">
                    <span aria-hidden="true">&raquo;</span>
                </a>
            </li>
        <?php endif ?>
    </ul>
</nav>
