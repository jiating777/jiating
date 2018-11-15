#include <stdio.h>
int exponentiation_mod(int a, int b, int c);
int gcd(int a, int b);
bool judge_e(int e, int t);


int main() {
	int p,q,e;   //用户输入 
	int n,o_n;    // t = n的欧拉函数 
	int d;      // d = 计算出的私钥 
	int m;      // m = 明文
	int c;      // c = 密文
			    
	char s;
	printf("请输入两个素数p,q: ");
	scanf("%d %d",&p,&q);           // 1. 输入p、q两个素数
	n = p*q;                        // 2. n = pq
	o_n = (p-1)*(q-1);                // 3. n的欧拉函数(n)=(p-1)(q-1)
	printf("请输入一个 e，它是素数，且与(n)互为素数: ");
	scanf("%d",&e);                 // 4. 输入随机整数e，满足e与其n的欧拉函数互素,且1<e< (n) 
	while(!judge_e(e,o_n)){
		printf("e不合法，请重新输入: ");
		scanf("%d",&e);
	}
	
	d = 1;
	while(((e*d)%o_n) != 1) 
		d++;                      // 5. 计算私钥d
	bool flag = false;
	printf("请输入要加密的明文m:\n");
	scanf("%d",&m);
	c = exponentiation_mod(m,e,n);  // 通过公式 C=Me mod n 求密文
	printf("m=%d加密后的密文c=%d\n",m,c);
	
	printf("请输入要解密的密文c:\n");
	scanf("%d",&c);
	m = exponentiation_mod(c,d,n);   // 通过公式求明文
	printf("c=%d解密后的明文m=%d\n",c,m); 
	
	return 0;
}

int exponentiation_mod(int a, int b, int c) {   // 模幂运算 
	int r = 1;
	b = b+1;
	while( b!=1 ){
		r = r*a;
		r = r%c;
		b--;
	}
	return r;
}

int gcd(int a, int b) {   // 求两个数的最大公约数 
	int tmp,c;
	if(a<b)	{
		tmp = a;
		a = b;
		b = tmp;
	}
	while ((a%b) != 0) {
		c = a%b;
		a = b;
		b = c;
	}
	return b;
}

bool judge_e(int e, int t) {  //判断e是否合法 
	if(gcd(e,t) != 1) {
		return false;
	}
	if(e<1 || e>t) {
		return false;
	}
	return true;
}


