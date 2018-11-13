#include<cstdio>
#include<cmath>
#include<iostream>

using namespace std;

struct coordinate {   // 定义二维坐标的结构体 
	int x;
	int y;
};

bool judge_p(int p, int a, int b);
coordinate add(coordinate p,coordinate g,int a,int n,int b);
void encrypt(coordinate g,coordinate P,coordinate m,int a,int r,int p);
int f1(coordinate P,coordinate q,int a,int p);
int gcd(int a, int b, int &s,int &t);
coordinate m2coordinate(int m, int k, int a, int b, int p);
int sqr(int s);
int mod(int x, int a, int b, int p);


int main() 	{
	int a, b;   //设椭圆曲线方程为 y^2=x^3+a*x+b	
	int p;
	int p_a;   // 用户A的私钥
	int k;//随机数k
	coordinate g;  // 椭圆曲线的一个生成元 
	coordinate Pm;  //明文m对应到椭圆曲线上的点 
	int m,k2;  // 明文m和随机数k ,k在30~50之间 
	
	printf("请输入椭圆曲线方程a和b的值：\n");
	scanf("%d %d",&a,&b);
	printf("请给定一个素数p：\n");
	scanf("%d",&p);
	
	while(!judge_p(p,a,b)){
		printf("p不合法，请重新输入: ");
		scanf("%d",&p);
	}
	printf("请输入用户A的私钥Pa：\n");
	scanf("%d",&p_a);
	
	printf("请给定一个生成元G：\n");
	scanf("%d %d",&g.x,&g.y);
	coordinate P = g;
	P = add(P, g, a, p_a,p);
	printf("输出用户A的公钥P的值：\n");
	printf("(%d,%d)\n",P.x,P.y);

	
	printf("请输入明文m在椭圆曲线上的点:\n");
	scanf("%d",&m);
	Pm = m2coordinate(m,30,a,b,p);
	printf("(%d,%d)\n",Pm.x,Pm.y);
//	scanf("%d %d",&Pm.x,&Pm.y);

	printf("请输入k：\n");
	scanf("%d",&k);
	encrypt(g, P, Pm, a, k, p);
	return 0;
}

bool judge_p(int p, int a, int b) {  // 判断输入的素数 p 是否合法

    int i = 0;
    for (i=2; i<p; i++) {  // 若不为素数，返回false
        if (p%i == 0)
            return false;
    }
    if((4 * a ^ 3 + 27 * b ^ 2) % p == 0) {
    	return false; 
	}
    return true;
}

coordinate add(coordinate p,coordinate g,int a,int n,int b) {  //abel群内的加法运算 a为曲线方程a，n为乘数，b为素数p

	int k;
	int e;
	for (int i = 0; i < n-1; i++){  // 计算用户A的公钥 PA=nAG，这里 n = p_a,用户A的私钥 
		k = f1(p, g, a,b);
		e = p.x;
		p.x = (k*k - p.x - g.x+b)%b;
		while (p.x < 0) {
			p.x = p.x + b;
		}
		p.y = (k*(e - p.x) - p.y + b) % b;
		while (p.y < 0) {
			p.y = p.y + b;
		}
	}
	return p;


}

void encrypt(coordinate g,coordinate P,coordinate m,int a,int r,int p) {
	coordinate c1, c2;
	c1 = g;
	c1 = add(c1, g, a, r, p);
	c2 = P;
	c2 = add(c2, P, a, r, p);
	c2 = add(m, c2, a, 2, p);
	printf("m的密文为：\n");
	printf("{(%d,%d),(%d,%d)}\n",c1.x,c1.y,c2.x,c2.y);
}

int f1(coordinate P,coordinate q,int a,int p) {  //求 λ的值,这里的除法a/b=a*b^-1（modp）
	int k,c,b,e,t;
	if ((P.x==q.x)&&(P.y==q.y)) {
		c = ((3 * q.x*q.x + a)%p+p)%p;
		b= (2 *P.y);
		gcd(b, p, e,t);
		while (e < 0) {
			e = (e + p)% p;
		}
		k = (c*e+p)%p;
	} else {
		c = ((q.y - P.y) % p + p ) % p;
		b = ((q.x - P.x) % p + p) % p;
		gcd(b, p, e,t);
		while (e < 0) {
			e = (e + p) % p;
		}
		k = (c*e+p)%p;
	}
	return k;
}


int gcd(int a, int b, int &s, int &t) {
	if(a==0 && b==0) {
		return -1;
	}
	if(b == 0) {
		s = 1;
		t = 0;
		return 0;
	}
	int d = gcd(b,a%b,t,s);
	t -= a / b * s;
	return d;
}

int sqr(int s) {  // 判断一个数是否是平方数 
	int m = 1;
    while(m*m < s) m++;
    if(m*m == s) {
    	return m;
	} else {
		return -1;
	}
}

int mod(int x, int a, int b, int p) {	
	__int64 tt = 2580*2580%4177*2580+(3*2580)%4177;
	printf("%d",tt%4177);
}


coordinate m2coordinate(int m, int k, int a, int b, int p) {  // 把明文转换到椭圆曲线上,x^3+a*x+b

	int x = m*k;
	int s = x*x*x + a*x + b;
	int j = 1;
	coordinate res;
	while(sqr(s%p) == -1) {
		x += j;
		s = x*x*x + a*x + b;
	}
	res.x = x;
	res.y = mod(x,p,a,b);
	return res;
}

