begin;

create table public.tasks (
    id uuid primary key default gen_random_uuid(),
    user_id uuid not null default auth.uid() references auth.users(id) on delete cascade,
    title text not null check (char_length(btrim(title)) between 1 and 120),
    description text not null default '' check (char_length(description) <= 2000),
    status text not null default 'Pending'
        check (status in ('Pending', 'In Progress', 'Completed')),
    due_date date,
    created_at timestamptz not null default now()
);

create index tasks_owner_created_idx on public.tasks (user_id, created_at desc, id desc);
alter table public.tasks enable row level security;
revoke all on public.tasks from public, anon, authenticated;
grant select, delete on public.tasks to authenticated;
grant insert (title, description, status, due_date),
      update (title, description, status, due_date) on public.tasks to authenticated;

create policy "Read own tasks" on public.tasks for select to authenticated
    using ((select auth.uid()) = user_id);
create policy "Create own tasks" on public.tasks for insert to authenticated
    with check ((select auth.uid()) = user_id);
create policy "Update own tasks" on public.tasks for update to authenticated
    using ((select auth.uid()) = user_id)
    with check ((select auth.uid()) = user_id);
create policy "Delete own tasks" on public.tasks for delete to authenticated
    using ((select auth.uid()) = user_id);

commit;
