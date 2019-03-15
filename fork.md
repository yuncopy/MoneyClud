## 什么是进程

> 通俗点讲: 进行中的程序（ 磁盘中静态的代码文件被cpu动态加载到内存 )

进程包括：代码段+数据段+堆栈段+PCB( 进程控制块 )

### PCB进程控制块:

进程控制块(PCB)是系统为了管理进程设置的一个专门的数据结构。系统用它来记录进程的外部特征，描述进程的运动变化过程。同时，系统可以利用PCB来控制和管理进程，所以说，PCB（进程控制块）是系统感知进程存在的唯一标志

### 进程的作用?

充分利用系统资源( cpu + 内存 )完成更多的任务。如：双开QQ，游戏多开，多进程运算（如我们的price价格运算 )等

使用linux系统提供的fork函数, 返回值: 成功创建一个子进程, 对于父进程返回子进程id, 对于子进程返回0, 创建失败返回0

```c
#include <stdio.h>
#include <unistd.h>
#include <sys/types.h>
#include <unistd.h>
#include <stdlib.h>

int main( int argc, char* argv[] ) {

    pid_t pid;
    pid = fork();
    if( -1 == pid ) {
        printf( "fork error" );
    }else if( 0 == pid ) {
        //child process
        printf( "this is child proccess:child_pid=%d,parent_pid=%d\n", getpid(), getppid() );
        exit( 0 );
    }else {
        //parent proccess
        printf( "this is parent proccess:child_pid=%d,parent_pid=%d\n", pid, getpid() );
    }
    return 0;
}
```

运行上面这段程序，你会发现，子进程的父进程ID不是 开启他（子进程)的父进程,  为什么呢？ 因为父进程先运行，子进程还没有来得及运行，父进程已经退出了.  这个时候子进程的ID就变成了父进程的父进程ID



### 一、通过sleep让父进程阻塞

sleep让父进程阻塞，保证子进程退出的时候，父进程还在运行，就能看到打印的父子进程ID的关系

```c
#include <stdio.h>
#include <unistd.h>
#include <sys/types.h>
#include <unistd.h>
#include <stdlib.h>

int main( int argc, char* argv\[\] ) {
pid_t pid;
pid = fork();
if( -1 == pid ) {
    printf( "fork error" );
}else if( 0 == pid ) {
    //child process
    printf( "this is child proccess:child_pid=%d,parent_pid=%d\n", getpid(), getppid() );
    exit( 0 );
}else {
    //parent proccess
    printf( "this is parent proccess:child_pid=%d,parent_pid=%d\n", pid, getpid() );
    sleep( 5 );
}
return 0;
}
```

上述，子进程并没有真正的退出，从下图可以看出, 子进程a.out变成defunct状态， 这个叫做僵尸进程。 

```shell
ghostwu@ghostwu:~/c_program/linux/proccess$ ps -ef | grep a.out
ghostwu   4041  3000  0 23:03 pts/8    00:00:00 ./a.out
ghostwu   4042  4041  0 23:03 pts/8    00:00:00 [a.out] <defunct>
ghostwu   4044  4024  0 23:03 pts/19   00:00:00 grep --color=auto a.out

```

### 二、 孤儿进程与僵进程

孤儿进程: 父进程在子进程之前退出

僵进程: 子进程在父进程之前退出，但是父进程不知道子进程退出了( 没有捕获到子进程的 退出状态 )

所以子进程退出，要告诉父进程，可以通过信号，可以避免僵进程的产生

```c
#include <stdio.h>
#include <unistd.h>
#include <sys/types.h>
#include <unistd.h>
#include <stdlib.h>
#include <signal.h>

int main( int argc, char* argv[] ) {

	signal( SIGCHLD,SIG_IGN );	

	pid_t pid;
	pid = fork();
	if( -1 == pid ) {
		printf( "fork error" );
	}else if( 0 == pid ) {
		//child process
		printf( "this is child proccess:child_pid=%d,parent_pid=%d\n", getpid(), getppid() );
		exit( 0 );
	}else {
		//parent proccess
		printf( "this is parent proccess:child_pid=%d,parent_pid=%d\n", pid, getpid() );
		sleep( 5 );
	}
	return 0;
}

```

这个时候才是真正的退出

```shell
ghostwu@ghostwu:~/c_program/linux/proccess$ ps -ef | grep a.out
ghostwu   4238  3000  0 23:09 pts/8    00:00:00 ./a.out
ghostwu   4241  4024  0 23:09 pts/19   00:00:00 grep --color=auto a.out
```

### 三、写时复制

设置一个全局变量，通过子进程修改他，然后观察父进程的变量是否会受到影响

```c
#include <stdio.h>
#include <unistd.h>
#include <sys/types.h>
#include <unistd.h>
#include <stdlib.h>
#include <signal.h>

int global_val = 10;

int main( int argc, char* argv[] ) {

	signal( SIGCHLD,SIG_IGN );	

	pid_t pid;
	pid = fork();
	if( -1 == pid ) {
		printf( "fork error" );
	}else if( 0 == pid ) {
		global_val++; //child process modify value
		printf( "child:global_val=%d\n", global_val );
		//child process
		printf( "this is child proccess:child_pid=%d,parent_pid=%d\n", getpid(), getppid() );
		exit( 0 );
	}else {
		sleep( 2 ); //make that child proccess run first
		//parent proccess
		printf( "this is parent proccess:child_pid=%d,parent_pid=%d\n", pid, getpid() );
		printf( "parent:global_val=%d\n", global_val );
		sleep( 3 );
	}
	return 0;
}

```

运行结果:

```shell
ghostwu@ghostwu:~/c_program/linux/proccess$ ./a.out 
child:global_val=11
this is child proccess:child_pid=4499,parent_pid=4498
this is parent proccess:child_pid=4499,parent_pid=4498
parent:global_val=10
```

上述结果表明，子进程修改数据 并不会影响父进程，因为fork子进程的时候，修改数据的时候，拷贝了一份global_val到子进程，所以操作子进程的global_val并不会影响父进程的global_val



### 四、进程小实例

开启10个进程，调用php执行文件index.php

```c
#include <stdio.h>
#include <unistd.h>
#include <sys/types.h>
#include <unistd.h>
#include <stdlib.h>
#include <signal.h>
#include <unistd.h>

int main( int argc, char* argv[] ) {

	signal( SIGCHLD,SIG_IGN );	
	int i = 0;
	for( i = 0; i < 10; i++ ){
			pid_t pid;
			pid = fork();
			if( -1 == pid ) {
				printf( "fork error" );
			}else if( 0 == pid ) {
				//printf( "child proccess:%d\n", (i+1) );
				execl( "/usr/bin/php", "php", "index.php", NULL );
				sleep( 10 );
				//exit( 0 );
			}else {
			}
	}
	return 0;
}

```

### 进程相关内容还有非常,非常多.....


























