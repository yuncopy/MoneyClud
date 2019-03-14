### docker
    文件目录结构
### git如何正确回滚代码

方法一，删除远程分支再提交

①首先两步保证当前工作区是干净的，并且和远程分支代码一致

$ git co currentBranch

$ git pull origin currentBranch

$ git co ./

②备份当前分支（如有必要）

$ git branch currentBranchBackUp

③恢复到指定的commit hash

$ git reset --hard resetVersionHash //将当前branch的HEAD指针指向commit hash

④删除当前分支的远程分支

$ git push origin :currentBranch

$ //或者这么写git push origin --delete currentBranch

⑤把当前分支提交到远程

$ git push origin currentBranch

02

方法二，强制push远程分支

①首先两步保证当前工作区是干净的，并且和远程分支代码一致

②备份当前分支（如有必要）

③恢复到指定的commit hash

$ git reset --hard resetVersionHash

④把当前分支强制提交到远程
备注：(目前回滚本地仓库代码，如果不强行推送到远程会提示，由于当前分支的提示落后，更新被拒绝，提示你需要git pull，如果pull代码就会覆盖本地代码)

$ git push -f origin currentBranch


不使用强制推送
不使用强制推送--100%



